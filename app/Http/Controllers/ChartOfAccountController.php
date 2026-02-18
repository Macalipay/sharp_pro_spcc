<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\AccountType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $chart_of_accounts = ChartOfAccount::orderBy('id', 'desc')->get();
        $account_type_structure = [
            'ASSETS' => ['Current Assets', 'Fixed Assets', 'Inventory', 'Non-current Assets'],
            'LIABILITY' => ['Current Liability', 'Liability', 'Non-current Liabilities'],
            'EQUITY' => ['Equity'],
            'EXPENSES' => ['Depreciation', 'Direct Costs', 'Expense', 'Overhead'],
            'REVENUE' => ['Revenue', 'Sales', 'Other Income'],
        ];

        $this->ensureStructuredAccountTypes($account_type_structure);

        $account_types = AccountType::orderBy('category')->orderBy('account_type')->get();
        $account_types_by_category = [];
        foreach ($account_type_structure as $category => $types) {
            $account_types_by_category[$category] = [];
            foreach ($types as $typeName) {
                $record = $account_types
                    ->first(function ($item) use ($category, $typeName) {
                        return strtoupper((string) $item->category) === strtoupper($category)
                            && strcasecmp((string) $item->account_type, $typeName) === 0;
                    });

                if ($record) {
                    $account_types_by_category[$category][] = $record;
                }
            }
        }

        return view('backend.pages.accounting.maintenance.chart_of_account', compact('chart_of_accounts', 'account_types', 'account_types_by_category'), ["type"=>"full-view"]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'account_number' => preg_replace('/\D+/', '', (string) $request->account_number),
            'description' => (string) ($request->description ?? ''),
        ]);

        $request->validate(
            [
                'account_type' => ['required', 'exists:account_types,id'],
                'account_number' => [
                    'required',
                    'regex:/^[0-9]+$/',
                    'max:10',
                    Rule::unique('chart_of_accounts', 'account_number')->whereNull('deleted_at'),
                ],
                'account_name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string'],
                'tax' => ['nullable', 'in:VAT,NON-VAT'],
                'allow_for_payments' => ['nullable', 'boolean'],
            ],
            [
                'account_number.regex' => 'Account Code must contain numbers only.',
                'account_number.max' => 'Account Code must not exceed 10 digits.',
                'account_number.unique' => 'Account Code ' . $request->account_number . ' already exists.',
                'account_name.max' => 'Account Name must not exceed 150 characters.',
            ]
        );

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
        $request['normal_balance'] = $request->normal_balance ?: 'DEBIT';
        $request['allow_for_payments'] = (int) ($request->get('allow_for_payments') ? 1 : 0);
        $request['is_system_locked'] = 0;
        $request['system_key'] = null;
        $request['allow_manual_journal_posting'] = 1;

        $account = ChartOfAccount::create($request->all());

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Successfully Added',
                'data' => [
                    'id' => $account->id,
                    'account_name' => $account->account_name,
                    'account_number' => $account->account_number,
                ],
            ]);
        }

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            $query = ChartOfAccount::with('account_type')->orderBy('id', 'desc');
            if ((int) request()->get('for_manual_journal', 0) === 1) {
                $query->where(function ($q) {
                    $q->whereNull('allow_manual_journal_posting')
                        ->orWhere('allow_manual_journal_posting', 1);
                });
            }
            return datatables()->of($query->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $chart_of_accounts = ChartOfAccount::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('chart_of_accounts'));
    }

    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        if ((int) ($account->is_system_locked ?? 0) === 1) {
            return response()->json(['message' => 'This is a system-locked control account and cannot be edited.'], 422);
        }

        $request->merge([
            'account_number' => preg_replace('/\D+/', '', (string) $request->account_number),
            'description' => (string) ($request->description ?? ''),
        ]);

        $request->validate(
            [
                'account_type' => ['required', 'exists:account_types,id'],
                'account_number' => [
                    'required',
                    'regex:/^[0-9]+$/',
                    'max:10',
                    Rule::unique('chart_of_accounts', 'account_number')
                        ->ignore($id)
                        ->whereNull('deleted_at'),
                ],
                'account_name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string'],
                'tax' => ['nullable', 'in:VAT,NON-VAT'],
                'allow_for_payments' => ['nullable', 'boolean'],
            ],
            [
                'account_number.regex' => 'Account Code must contain numbers only.',
                'account_number.max' => 'Account Code must not exceed 10 digits.',
                'account_number.unique' => 'Account Code ' . $request->account_number . ' already exists.',
                'account_name.max' => 'Account Name must not exceed 150 characters.',
            ]
        );

        $payload = $request->all();
        $payload['normal_balance'] = $request->normal_balance ?: 'DEBIT';
        $payload['allow_for_payments'] = (int) ($request->get('allow_for_payments') ? 1 : 0);

        $account->update($payload);
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            $account = ChartOfAccount::find($item);
            if (!$account) {
                continue;
            }
            if ((int) ($account->is_system_locked ?? 0) === 1) {
                return response('System-locked control accounts cannot be deleted.', 422);
            }
            $account->delete();
        }

        return 'Record Deleted';
    }

    private function ensureStructuredAccountTypes(array $structure): void
    {
        foreach ($structure as $category => $types) {
            foreach ($types as $typeName) {
                $existing = AccountType::withTrashed()
                    ->whereRaw('UPPER(category) = ?', [strtoupper($category)])
                    ->whereRaw('LOWER(account_type) = ?', [strtolower($typeName)])
                    ->first();

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                    }
                    continue;
                }

                AccountType::create([
                    'category' => $category,
                    'account_type' => $typeName,
                    'workstation_id' => Auth::user()->workstation_id,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }
        }
    }
}
