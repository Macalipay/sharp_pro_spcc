<?php

namespace App\Http\Controllers;

use App\AccountingBill;
use App\AccountingBillHistory;
use App\AccountingBillItem;
use App\AccountingBillNote;
use App\AccountingBillPayment;
use App\ChartOfAccount;
use App\JournalEntry;
use App\JournalEntryLineField;
use App\PayrollSummary;
use App\PayrollSummaryDetails;
use App\Supplier;
use App\Workstation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingBillController extends Controller
{
    public function index(Request $request)
    {
        $statusTab = strtoupper((string) $request->get('tab', AccountingBill::STATUS_DRAFT));
        $allowedTabs = [
            AccountingBill::STATUS_DRAFT,
            AccountingBill::STATUS_AWAITING_APPROVAL,
            AccountingBill::STATUS_AWAITING_PAYMENT,
            AccountingBill::STATUS_PAID,
        ];
        if (!in_array($statusTab, $allowedTabs, true)) {
            $statusTab = AccountingBill::STATUS_DRAFT;
        }
        $mode = strtolower((string) $request->get('mode', 'list'));
        if (!in_array($mode, ['list', 'create', 'edit'], true)) {
            $mode = 'list';
        }
        $preselectedSupplierId = (int) $request->get('supplier_id', 0);

        $user = Auth::user();
        $workstationId = $user ? $user->workstation_id : null;

        $this->syncSubmittedPayrollsToDraftExpenses($user);

        $billQuery = AccountingBill::with(['supplier', 'created_user', 'approved_user', 'paid_user'])
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc');
        if (!empty($workstationId)) {
            $billQuery->where('workstation_id', $workstationId);
        }
        $allBills = $billQuery->get();

        $grouped = [
            AccountingBill::STATUS_DRAFT => $allBills->where('status', AccountingBill::STATUS_DRAFT)->values(),
            AccountingBill::STATUS_AWAITING_APPROVAL => $allBills->where('status', AccountingBill::STATUS_AWAITING_APPROVAL)->values(),
            AccountingBill::STATUS_AWAITING_PAYMENT => $allBills->where('status', AccountingBill::STATUS_AWAITING_PAYMENT)->values(),
            AccountingBill::STATUS_PAID => $allBills->where('status', AccountingBill::STATUS_PAID)->values(),
        ];

        $editBill = null;
        $editBillId = (int) $request->get('bill_id', 0);
        if ($mode === 'edit' && $editBillId > 0) {
            $editBill = AccountingBill::with(['created_user', 'items', 'payments.payment_account', 'payments.creator', 'notes.author', 'histories.actor'])
                ->whereNull('deleted_at')
                ->find($editBillId);
            if (!$editBill || $editBill->status !== AccountingBill::STATUS_DRAFT) {
                $editBill = null;
                $mode = 'list';
            } else {
                $editBill->paid_amount = (float) $editBill->payments->sum('amount');
                $editBill->remaining_amount = max((float) $editBill->total_amount - $editBill->paid_amount, 0);
            }
        }

        $suppliers = Supplier::orderBy('supplier_name', 'asc')->get();
        $expenseAndAssetAccounts = ChartOfAccount::with('account_type')
            ->leftJoin('account_types as at', 'at.id', '=', 'chart_of_accounts.account_type')
            ->whereNull('chart_of_accounts.deleted_at')
            ->whereRaw("UPPER(COALESCE(at.category,'')) IN ('ASSETS','EXPENSES')")
            ->select('chart_of_accounts.*')
            ->orderBy('chart_of_accounts.account_name', 'asc')
            ->get();
        $paymentAccounts = ChartOfAccount::with('account_type')
            ->whereNull('chart_of_accounts.deleted_at')
            ->where(function ($q) {
                $q->where('allow_for_payments', 1)
                    ->orWhereRaw("LOWER(COALESCE(account_name,'')) LIKE '%cash%'")
                    ->orWhereRaw("LOWER(COALESCE(account_name,'')) LIKE '%bank%'");
            })
            ->orderBy('account_name', 'asc')
            ->get();

        return view('backend.pages.accounting.transaction.bills', compact(
            'statusTab',
            'mode',
            'preselectedSupplierId',
            'grouped',
            'editBill',
            'suppliers',
            'expenseAndAssetAccounts',
            'paymentAccounts'
        ), ["type" => "full-view"]);
    }

    private function syncSubmittedPayrollsToDraftExpenses($user): void
    {
        if (!$user) {
            return;
        }

        $summaries = PayrollSummary::query()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('workflow_status', 3)
                    ->orWhere(function ($q) {
                        $q->whereRaw('COALESCE(workflow_status, 0) = 0')
                            ->where('status', 2);
                    });
            })
            ->where(function ($q) use ($user) {
                if (!empty($user->workstation_id)) {
                    $q->where('workstation_id', $user->workstation_id)
                        ->orWhereNull('workstation_id');
                }
            })
            ->get();

        if ($summaries->isEmpty()) {
            return;
        }

        $expenseAccountId = ChartOfAccount::query()
            ->leftJoin('account_types as at', 'at.id', '=', 'chart_of_accounts.account_type')
            ->whereNull('chart_of_accounts.deleted_at')
            ->where(function ($q) {
                $q->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                    ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) IN ('EXPENSE','EXPENSES','DIRECT COSTS','DEPRECIATION','OVERHEAD')");
            })
            ->orderBy('chart_of_accounts.id', 'asc')
            ->value('chart_of_accounts.id');
        $apAccountId = ChartOfAccount::query()
            ->whereNull('deleted_at')
            ->where('system_key', 'ACCOUNTS_PAYABLE_CONTROL')
            ->value('id');

        foreach ($summaries as $summary) {
            $totalNetPay = (float) PayrollSummaryDetails::query()
                ->whereNull('deleted_at')
                ->where('summary_id', $summary->id)
                ->sum('net_pay');

            if ($totalNetPay <= 0) {
                continue;
            }

            $marker = '[AUTO_PAYROLL_SUMMARY_ID:' . $summary->id . ']';
            $bill = AccountingBill::query()
                ->whereNull('deleted_at')
                ->where('description', 'like', '%' . $marker . '%')
                ->first();

            if ($bill && $bill->status !== AccountingBill::STATUS_DRAFT) {
                continue;
            }

            if (!$bill) {
                $bill = new AccountingBill();
                $bill->bill_no = 'PYR-' . preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($summary->sequence_no ?: $summary->id));
                $bill->created_by = $user->id;
            }

            $bill->bill_date = $summary->pay_date ?: ($summary->period_start ?: date('Y-m-d'));
            $bill->due_date = $bill->bill_date;
            $bill->description = 'Auto-created from payroll submitted for payment ' . ($summary->sequence_no ?: ('#' . $summary->id)) . ' ' . $marker;
            $bill->status = AccountingBill::STATUS_DRAFT;
            $bill->total_amount = $totalNetPay;
            $bill->accounts_payable_account_id = $apAccountId ?: $bill->accounts_payable_account_id;
            $bill->workstation_id = $summary->workstation_id ?: $user->workstation_id;
            $bill->updated_by = $user->id;
            $bill->save();

            AccountingBillItem::query()->where('accounting_bill_id', $bill->id)->delete();
            AccountingBillItem::create([
                'accounting_bill_id' => $bill->id,
                'chart_of_account_id' => $expenseAccountId ?: null,
                'description' => 'Payroll Expense - ' . ($summary->sequence_no ?: ('Summary #' . $summary->id)),
                'quantity' => 1,
                'unit_price' => $totalNetPay,
                'line_total' => $totalNetPay,
                'workstation_id' => $bill->workstation_id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }
    }

    public function createSupplier(Request $request)
    {
        $returnTab = strtoupper((string) $request->get('tab', AccountingBill::STATUS_DRAFT));
        $allowedTabs = [
            AccountingBill::STATUS_DRAFT,
            AccountingBill::STATUS_AWAITING_APPROVAL,
            AccountingBill::STATUS_AWAITING_PAYMENT,
            AccountingBill::STATUS_PAID,
        ];
        if (!in_array($returnTab, $allowedTabs, true)) {
            $returnTab = AccountingBill::STATUS_DRAFT;
        }

        $returnMode = strtolower((string) $request->get('mode', 'create'));
        if (!in_array($returnMode, ['create', 'edit'], true)) {
            $returnMode = 'create';
        }
        $returnBillId = (int) $request->get('bill_id', 0);

        return view('backend.pages.accounting.transaction.bill_supplier_create', compact('returnTab', 'returnMode', 'returnBillId'), ["type" => "full-view"]);
    }

    public function saveSupplier(Request $request)
    {
        $request->validate([
            'supplier_name' => ['required', 'string'],
            'contact_no' => ['required', 'string'],
            'contact_person' => ['required', 'string'],
            'address' => ['required', 'string'],
            'tin_no' => ['required', 'string'],
            'payment_terms' => ['required', 'string'],
            'bank_name' => ['nullable', 'string'],
            'bank_account' => ['nullable', 'string'],
            'vatable' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $workstationId = $user && $user->workstation_id
            ? $user->workstation_id
            : Workstation::query()->value('id');
        $actorId = $user ? $user->id : 1;

        try {
            $supplier = Supplier::create([
                'supplier_name' => $request->supplier_name,
                'contact_no' => $request->contact_no,
                'contact_person' => $request->contact_person,
                'address' => $request->address,
                'tin_no' => $request->tin_no,
                'payment_terms' => $request->payment_terms,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'vatable' => $request->vatable,
                'workstation_id' => $workstationId,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed saving supplier from bills modal', [
                'message' => $e->getMessage(),
                'user_id' => $user ? $user->id : null,
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Failed to save supplier record.',
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to save supplier record.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Supplier created successfully.',
                'data' => [
                    'id' => $supplier->id,
                    'supplier_name' => $supplier->supplier_name,
                ],
            ]);
        }

        $returnTab = strtoupper((string) $request->get('return_tab', AccountingBill::STATUS_DRAFT));
        $returnMode = strtolower((string) $request->get('return_mode', 'create'));
        if (!in_array($returnMode, ['create', 'edit'], true)) {
            $returnMode = 'create';
        }
        $returnBillId = (int) $request->get('return_bill_id', 0);

        $redirect = '/accounting/bills?tab=' . $returnTab . '&mode=' . $returnMode . '&supplier_id=' . $supplier->id;
        if ($returnMode === 'edit' && $returnBillId > 0) {
            $redirect .= '&bill_id=' . $returnBillId;
        }

        return redirect($redirect)
            ->with('success', 'Supplier created successfully.');
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $billId = (int) $request->get('bill_id', 0);
        $action = strtolower((string) $request->get('action_type', 'draft'));
        $isSubmit = $action === 'submit';

        $bill = null;
        if ($billId > 0) {
            $bill = AccountingBill::with('items')->whereNull('deleted_at')->findOrFail($billId);
            if ($bill->status !== AccountingBill::STATUS_DRAFT) {
                return redirect()->back()->with('error', 'Only Draft bills can be edited.');
            }
        }

        $rules = [
            'bill_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ];
        if ($isSubmit) {
            $rules['supplier_id'] = ['required', 'integer'];
            $rules['bill_date'] = ['required', 'date'];
            $rules['due_date'] = ['required', 'date'];
        } else {
            $rules['supplier_id'] = ['nullable', 'integer'];
        }
        $request->validate($rules);

        $lineItems = $this->extractLineItems($request);
        $totalAmount = collect($lineItems)->sum('line_total');
        if ($isSubmit) {
            if (count($lineItems) === 0) {
                return redirect()->back()->with('error', 'At least one line item is required for approval.');
            }
            if ($totalAmount <= 0) {
                return redirect()->back()->with('error', 'Total amount must be greater than zero.');
            }
        }

        DB::transaction(function () use ($request, $user, $bill, $lineItems, $totalAmount, $isSubmit) {
            $apAccount = $this->getAccountsPayableControlAccount(
                $user ? $user->workstation_id : null,
                $user ? $user->id : null
            );
            $isNew = !$bill;
            $record = $bill ?: new AccountingBill();
            $record->bill_no = $record->bill_no ?: $this->generateBillNo();
            $record->supplier_id = $request->supplier_id ?: null;
            $record->bill_date = $request->bill_date ?: null;
            $record->due_date = $request->due_date ?: null;
            $record->description = $request->description ?: null;
            $record->accounts_payable_account_id = $apAccount ? $apAccount->id : null;
            $record->total_amount = $totalAmount;
            $record->status = $isSubmit ? AccountingBill::STATUS_AWAITING_APPROVAL : AccountingBill::STATUS_DRAFT;
            if ($isSubmit) {
                $record->submitted_by = $user ? $user->id : null;
                $record->submitted_at = Carbon::now();
            }
            $record->workstation_id = $user ? $user->workstation_id : null;
            if (!$record->exists) {
                $record->created_by = $user ? $user->id : null;
            }
            $record->updated_by = $user ? $user->id : null;
            $record->save();

            AccountingBillItem::where('accounting_bill_id', $record->id)->delete();
            foreach ($lineItems as $item) {
                AccountingBillItem::create([
                    'accounting_bill_id' => $record->id,
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'workstation_id' => $user ? $user->workstation_id : null,
                    'created_by' => $user ? $user->id : null,
                    'updated_by' => $user ? $user->id : null,
                ]);
            }

            if ($isNew) {
                $this->addHistory($record, 'CREATED', 'Bill created.', null, $user ? $user->id : null);
            } else {
                $this->addHistory($record, 'EDITED', 'Bill updated.', null, $user ? $user->id : null);
            }
            if ($isSubmit) {
                $this->addHistory($record, 'SUBMITTED', 'Submitted for approval.', null, $user ? $user->id : null);
            }
        });

        return redirect('/accounting/bills?tab=' . ($isSubmit ? AccountingBill::STATUS_AWAITING_APPROVAL : AccountingBill::STATUS_DRAFT))
            ->with('success', $isSubmit ? 'Bill submitted for approval.' : 'Draft bill saved.');
    }

    public function destroy($id)
    {
        $bill = AccountingBill::whereNull('deleted_at')->findOrFail($id);
        if ($bill->status !== AccountingBill::STATUS_DRAFT) {
            return redirect()->back()->with('error', 'Only Draft bills can be deleted.');
        }
        $bill->delete();
        return redirect()->back()->with('success', 'Draft bill deleted.');
    }

    public function submit($id)
    {
        $user = Auth::user();
        $bill = AccountingBill::with('items')->whereNull('deleted_at')->findOrFail($id);
        if ($bill->status !== AccountingBill::STATUS_DRAFT) {
            return redirect()->back()->with('error', 'Only Draft bills can be submitted.');
        }

        $validationError = $this->validateBillForPosting($bill);
        if (!empty($validationError)) {
            return redirect()->back()->with('error', $validationError);
        }

        $bill->status = AccountingBill::STATUS_AWAITING_APPROVAL;
        $bill->submitted_by = $user ? $user->id : null;
        $bill->submitted_at = Carbon::now();
        $bill->updated_by = $user ? $user->id : null;
        $bill->save();
        $this->addHistory($bill, 'SUBMITTED', 'Submitted for approval.', null, $user ? $user->id : null);

        return redirect('/accounting/bills?tab=' . AccountingBill::STATUS_AWAITING_APPROVAL)
            ->with('success', 'Bill submitted for approval.');
    }

    public function approve($id)
    {
        $user = Auth::user();
        $bill = AccountingBill::with(['items'])->whereNull('deleted_at')->findOrFail($id);
        if ($bill->status !== AccountingBill::STATUS_AWAITING_APPROVAL) {
            return redirect()->back()->with('error', 'Only Awaiting Approval bills can be approved.');
        }

        $validationError = $this->validateBillForPosting($bill);
        if (!empty($validationError)) {
            return redirect()->back()->with('error', $validationError);
        }

        DB::transaction(function () use ($bill, $user) {
            $journal = $this->createRecognitionJournal($bill, $user);
            $bill->status = AccountingBill::STATUS_AWAITING_PAYMENT;
            $bill->approved_by = $user ? $user->id : null;
            $bill->approved_at = Carbon::now();
            $bill->recognition_journal_entry_id = $journal->id;
            $bill->updated_by = $user ? $user->id : null;
            $bill->save();
            $this->addHistory($bill, 'APPROVED', 'Bill approved and moved to Awaiting Payment.', null, $user ? $user->id : null);
        });

        return redirect('/accounting/bills?tab=' . AccountingBill::STATUS_AWAITING_PAYMENT)
            ->with('success', 'Bill approved and posted to Accounts Payable.');
    }

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $bill = AccountingBill::whereNull('deleted_at')->findOrFail($id);
        if ($bill->status !== AccountingBill::STATUS_AWAITING_APPROVAL) {
            return redirect()->back()->with('error', 'Only Awaiting Approval bills can be rejected.');
        }

        $bill->status = AccountingBill::STATUS_DRAFT;
        $bill->rejected_by = $user ? $user->id : null;
        $bill->rejected_at = Carbon::now();
        $bill->rejected_reason = $request->rejected_reason ?: null;
        $bill->updated_by = $user ? $user->id : null;
        $bill->save();
        $this->addHistory($bill, 'REJECTED', 'Bill rejected and returned to Draft.', null, $user ? $user->id : null);

        return redirect('/accounting/bills?tab=' . AccountingBill::STATUS_DRAFT)
            ->with('success', 'Bill returned to Draft.');
    }

    public function pay(Request $request, $id)
    {
        $user = Auth::user();
        $bill = AccountingBill::with(['items'])->whereNull('deleted_at')->findOrFail($id);
        if ($bill->status !== AccountingBill::STATUS_AWAITING_PAYMENT) {
            return redirect()->back()->with('error', 'Only Awaiting Payment bills can be paid.');
        }

        $request->validate([
            'payment_account_id' => ['required', 'integer'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_reference' => ['required', 'string', 'max:255'],
            'payment_date' => ['required', 'date'],
        ]);

        $paidAmount = (float) $bill->payments()->sum('amount');
        $remaining = max((float) $bill->total_amount - $paidAmount, 0);
        $paymentAmount = round((float) $request->payment_amount, 2);
        if ($paymentAmount > $remaining) {
            return redirect()->back()->with('error', 'Payment amount cannot exceed remaining balance.');
        }

        DB::transaction(function () use ($bill, $request, $user) {
            $bill->payment_account_id = $request->payment_account_id;
            $paymentAmount = round((float) $request->payment_amount, 2);
            $paymentJournal = $this->createPaymentJournal($bill, $request->payment_date, $user, $paymentAmount);
            AccountingBillPayment::create([
                'accounting_bill_id' => $bill->id,
                'amount' => $paymentAmount,
                'payment_date' => Carbon::parse($request->payment_date)->format('Y-m-d'),
                'payment_account_id' => $request->payment_account_id,
                'payment_reference' => $request->payment_reference,
                'journal_entry_id' => $paymentJournal->id,
                'created_by' => $user ? $user->id : null,
                'updated_by' => $user ? $user->id : null,
            ]);

            $totalPaid = (float) $bill->payments()->sum('amount');
            $remaining = max((float) $bill->total_amount - $totalPaid, 0);

            $bill->status = $remaining > 0 ? AccountingBill::STATUS_AWAITING_PAYMENT : AccountingBill::STATUS_PAID;
            $bill->payment_reference = $request->payment_reference;
            if ($remaining <= 0.00001) {
                $bill->paid_by = $user ? $user->id : null;
                $bill->paid_at = Carbon::parse($request->payment_date)->endOfDay();
            }
            $bill->payment_journal_entry_id = $paymentJournal->id;
            $bill->updated_by = $user ? $user->id : null;
            $bill->save();
            $this->addHistory(
                $bill,
                'PAYMENT_ADDED',
                'Payment added. Remaining balance: ' . number_format($remaining, 2),
                $paymentAmount,
                $user ? $user->id : null
            );
        });

        return redirect('/accounting/bills/show/' . $bill->id)
            ->with('success', 'Payment applied successfully.');
    }

    public function show($id)
    {
        $bill = AccountingBill::with([
            'supplier',
            'items.account',
            'payable_account',
            'payment_account',
            'created_user',
            'approved_user',
            'paid_user',
            'payments.payment_account',
            'payments.creator',
            'histories.actor',
            'notes.author',
        ])->whereNull('deleted_at')->findOrFail($id);

        $bill->paid_amount = (float) $bill->payments->sum('amount');
        $bill->remaining_amount = max((float) $bill->total_amount - $bill->paid_amount, 0);

        $paymentAccounts = ChartOfAccount::with('account_type')
            ->whereNull('chart_of_accounts.deleted_at')
            ->where(function ($q) {
                $q->where('allow_for_payments', 1)
                    ->orWhereRaw("LOWER(COALESCE(account_name,'')) LIKE '%cash%'")
                    ->orWhereRaw("LOWER(COALESCE(account_name,'')) LIKE '%bank%'");
            })
            ->orderBy('account_name', 'asc')
            ->get();

        return view('backend.pages.accounting.transaction.bill_show', compact('bill', 'paymentAccounts'), ["type" => "full-view"]);
    }

    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => ['required', 'string'],
        ]);

        $bill = AccountingBill::whereNull('deleted_at')->findOrFail($id);
        $user = Auth::user();

        AccountingBillNote::create([
            'accounting_bill_id' => $bill->id,
            'note' => $request->note,
            'added_by' => $user ? $user->id : null,
            'added_at' => Carbon::now(),
        ]);

        $this->addHistory($bill, 'NOTE_ADDED', 'User added a note.', null, $user ? $user->id : null);

        return redirect()->back()->with('success', 'Note added.');
    }

    private function extractLineItems(Request $request)
    {
        $descriptions = (array) $request->get('line_description', []);
        $accountIds = (array) $request->get('line_account_id', []);
        $quantities = (array) $request->get('line_qty', []);
        $unitPrices = (array) $request->get('line_unit_price', []);

        $items = [];
        $max = max(count($descriptions), count($accountIds), count($quantities), count($unitPrices));
        for ($i = 0; $i < $max; $i++) {
            $accountId = (int) ($accountIds[$i] ?? 0);
            $qty = (float) ($quantities[$i] ?? 0);
            $unitPrice = (float) ($unitPrices[$i] ?? 0);
            $lineTotal = round($qty * $unitPrice, 2);

            if ($accountId <= 0 && $lineTotal <= 0) {
                continue;
            }

            $items[] = [
                'chart_of_account_id' => $accountId > 0 ? $accountId : null,
                'description' => trim((string) ($descriptions[$i] ?? '')),
                'quantity' => $qty > 0 ? $qty : 1,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        return array_values(array_filter($items, function ($item) {
            return !empty($item['chart_of_account_id']) && $item['line_total'] > 0;
        }));
    }

    private function validateBillForPosting(AccountingBill $bill)
    {
        if (!$this->isAutoPayrollDraft($bill) && empty($bill->supplier_id)) {
            return 'Supplier is required.';
        }
        if (empty($bill->bill_date) || empty($bill->due_date)) {
            return 'Bill date and due date are required.';
        }
        if (empty($bill->accounts_payable_account_id)) {
            return 'Accounts Payable account is required.';
        }
        $bill->loadMissing('items');
        if (empty($bill->accounts_payable_account_id)) {
            $user = Auth::user();
            $apAccount = $this->getAccountsPayableControlAccount(
                $user ? $user->workstation_id : null,
                $user ? $user->id : null
            );
            if ($apAccount) {
                $bill->accounts_payable_account_id = $apAccount->id;
                $bill->save();
            }
        }
        if ($this->isAutoPayrollDraft($bill)) {
            $expenseAccountId = ChartOfAccount::query()
                ->leftJoin('account_types as at', 'at.id', '=', 'chart_of_accounts.account_type')
                ->whereNull('chart_of_accounts.deleted_at')
                ->where(function ($q) {
                    $q->whereRaw("UPPER(TRIM(COALESCE(at.category,''))) IN ('EXPENSES','EXPENSE')")
                        ->orWhereRaw("UPPER(TRIM(COALESCE(at.account_type,''))) IN ('EXPENSE','EXPENSES','DIRECT COSTS','DEPRECIATION','OVERHEAD')");
                })
                ->orderBy('chart_of_accounts.id', 'asc')
                ->value('chart_of_accounts.id');
            if ($expenseAccountId) {
                AccountingBillItem::query()
                    ->where('accounting_bill_id', $bill->id)
                    ->whereNull('deleted_at')
                    ->whereNull('chart_of_account_id')
                    ->update(['chart_of_account_id' => $expenseAccountId, 'updated_by' => Auth::id()]);
                $bill->load('items');
            }
        }
        if ($bill->items->count() === 0) {
            return 'At least one line item is required.';
        }
        if ((float) $bill->total_amount <= 0) {
            return 'Total amount must be greater than zero.';
        }
        return null;
    }

    private function isAutoPayrollDraft(AccountingBill $bill): bool
    {
        $description = (string) ($bill->description ?? '');
        return stripos($description, '[AUTO_PAYROLL_SUMMARY_ID:') !== false;
    }

    private function createRecognitionJournal(AccountingBill $bill, $user)
    {
        $total = (float) $bill->total_amount;

        $journal = JournalEntry::create([
            'entry_date' => $bill->bill_date,
            'reference_number' => 'BILL-AP-' . str_pad((string) $bill->id, 5, '0', STR_PAD_LEFT),
            'description' => 'BILL RECOGNITION - ' . ($bill->bill_no ?: ('BILL #' . $bill->id)),
            'total_debit' => $total,
            'total_credit' => $total,
            'status' => 'POSTED',
            'approved_by' => $user ? $user->id : null,
            'workstation_id' => $user ? $user->workstation_id : null,
            'created_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ]);

        foreach ($bill->items as $item) {
            JournalEntryLineField::create([
                'journal_entry_id' => $journal->id,
                'chart_of_account_id' => $item->chart_of_account_id,
                'data_type' => 'BILL',
                'data_id' => (string) $bill->id,
                'description' => $item->description ?: 'BILL ITEM',
                'debit_amount' => (string) $item->line_total,
                'credit_amount' => '0',
                'tax_rate' => null,
                'workstation_id' => $user ? $user->workstation_id : null,
                'created_by' => $user ? $user->id : null,
                'approved_by' => $user ? $user->id : null,
                'updated_by' => $user ? $user->id : null,
            ]);
        }

        JournalEntryLineField::create([
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $bill->accounts_payable_account_id,
            'data_type' => 'BILL',
            'data_id' => (string) $bill->id,
            'description' => 'ACCOUNTS PAYABLE',
            'debit_amount' => '0',
            'credit_amount' => (string) $total,
            'tax_rate' => null,
            'workstation_id' => $user ? $user->workstation_id : null,
            'created_by' => $user ? $user->id : null,
            'approved_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ]);

        return $journal;
    }

    private function createPaymentJournal(AccountingBill $bill, $paymentDate, $user, $amount)
    {
        $total = (float) $amount;
        $journal = JournalEntry::create([
            'entry_date' => Carbon::parse($paymentDate)->format('Y-m-d'),
            'reference_number' => 'BILL-PMT-' . str_pad((string) $bill->id, 5, '0', STR_PAD_LEFT),
            'description' => 'BILL PAYMENT - ' . ($bill->bill_no ?: ('BILL #' . $bill->id)),
            'total_debit' => $total,
            'total_credit' => $total,
            'status' => 'POSTED',
            'approved_by' => $user ? $user->id : null,
            'workstation_id' => $user ? $user->workstation_id : null,
            'created_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ]);

        JournalEntryLineField::create([
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $bill->accounts_payable_account_id,
            'data_type' => 'BILL',
            'data_id' => (string) $bill->id,
            'description' => 'CLEAR ACCOUNTS PAYABLE',
            'debit_amount' => (string) $total,
            'credit_amount' => '0',
            'tax_rate' => null,
            'workstation_id' => $user ? $user->workstation_id : null,
            'created_by' => $user ? $user->id : null,
            'approved_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ]);

        JournalEntryLineField::create([
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $bill->payment_account_id,
            'data_type' => 'BILL',
            'data_id' => (string) $bill->id,
            'description' => 'PAYMENT - CASH/BANK',
            'debit_amount' => '0',
            'credit_amount' => (string) $total,
            'tax_rate' => null,
            'workstation_id' => $user ? $user->workstation_id : null,
            'created_by' => $user ? $user->id : null,
            'approved_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ]);

        return $journal;
    }

    private function generateBillNo()
    {
        $next = (int) (AccountingBill::max('id') ?? 0) + 1;
        return 'BILL-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function addHistory(AccountingBill $bill, $action, $description = null, $amount = null, $userId = null)
    {
        AccountingBillHistory::create([
            'accounting_bill_id' => $bill->id,
            'action' => (string) $action,
            'description' => $description,
            'amount' => $amount,
            'performed_by' => $userId,
            'performed_at' => Carbon::now(),
        ]);
    }

    private function getAccountsPayableControlAccount($workstationId, $userId)
    {
        $account = ChartOfAccount::whereNull('deleted_at')
            ->where('system_key', 'ACCOUNTS_PAYABLE_CONTROL')
            ->first();
        if ($account) {
            return $account;
        }

        $liabilityType = DB::table('account_types')
            ->whereRaw("UPPER(COALESCE(category,'')) = 'LIABILITY'")
            ->orderBy('id', 'asc')
            ->value('id');
        if (!$liabilityType) {
            return null;
        }

        $candidate = 2000000001;
        while (ChartOfAccount::whereNull('deleted_at')->where('account_number', (string) $candidate)->exists()) {
            $candidate++;
        }

        return ChartOfAccount::create([
            'account_number' => (string) $candidate,
            'account_name' => 'Accounts Payable',
            'account_type' => $liabilityType,
            'description' => 'System-locked Accounts Payable control account',
            'tax' => null,
            'allow_for_payments' => 0,
            'is_system_locked' => 1,
            'system_key' => 'ACCOUNTS_PAYABLE_CONTROL',
            'allow_manual_journal_posting' => 0,
            'normal_balance' => 'CREDIT',
            'workstation_id' => $workstationId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }
}
