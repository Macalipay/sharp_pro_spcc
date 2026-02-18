<?php

namespace App\Http\Controllers;

use App\JournalEntry;
use App\ChartOfAccount;
use App\AccountType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Auth;

class JournalEntryController extends Controller
{
    public function index()
    {
        $journal_entries = JournalEntry::orderBy('id', 'desc')->get();
        $accounts = ChartOfAccount::where(function ($query) {
                $query->whereNull('allow_manual_journal_posting')
                    ->orWhere('allow_manual_journal_posting', 1);
            })
            ->orderBy('id')
            ->get();
        $account_types = AccountType::orderBy('id')->get();
        $account_type_structure = [
            'ASSETS' => ['Current Assets', 'Fixed Assets', 'Inventory', 'Non-current Assets'],
            'LIABILITY' => ['Current Liability', 'Liability', 'Non-current Liabilities'],
            'EQUITY' => ['Equity'],
            'EXPENSES' => ['Depreciation', 'Direct Costs', 'Expense', 'Overhead'],
            'REVENUE' => ['Revenue', 'Sales', 'Other Income'],
        ];

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

        return view(
            'backend.pages.accounting.transaction.journal_entry',
            compact('journal_entries', 'accounts', 'account_types', 'account_types_by_category'),
            ["type"=>"full-view"]
        );
    }

    public function store(Request $request)
    {
        $request->merge([
            'entry_date' => $this->normalizeJournalDateInput($request->input('entry_date'), false),
            'auto_reversing_date' => $this->normalizeJournalDateInput($request->input('auto_reversing_date'), true),
        ]);

        $request->validate([
            'entry_date' => ['required', 'date_format:Y-m-d'],
            'description' => ['required'],
            'auto_reversing_date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:DRAFT,POSTED'],
            'supporting_doc_data' => ['nullable', 'string'],
            'supporting_doc_name' => ['nullable', 'string', 'max:255'],
            'supporting_doc_mime' => ['nullable', 'string', 'max:100'],
        ]);

        if (empty($request->reference_number)) {
            $request['reference_number'] = 'JE-' . date('YmdHis');
        }

        if (empty($request->status)) {
            $request['status'] = 'DRAFT';
        }

        $request['supporting_document'] = $this->storeSupportingDocument(
            $request->supporting_doc_data,
            $request->supporting_doc_name
        );

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $entry = JournalEntry::create($request->all());

        if ($request->ajax()) {
            $last_record = ['id' => $entry->id];
            return response()->json(compact('last_record'));
        }

        return redirect()->back()->with('success','Successfully Added');
    }

    private function storeSupportingDocument($encodedFile, $originalName)
    {
        if (!$encodedFile) {
            return null;
        }

        if (!preg_match('/^data:([^;]+);base64,(.+)$/', $encodedFile, $matches)) {
            throw ValidationException::withMessages(['supporting_document' => ['Invalid attachment format.']]);
        }

        $mime = strtolower(trim($matches[1]));
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        $extension = $allowed[$mime] ?? null;
        if (!$extension) {
            $nameExtension = strtolower(pathinfo($originalName ?? '', PATHINFO_EXTENSION));
            $fallbackAllowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            if (!in_array($nameExtension, $fallbackAllowed, true)) {
                throw ValidationException::withMessages(['supporting_document' => ['Unsupported file type. Allowed: PDF, JPEG, PNG, DOC, DOCX.']]);
            }
            $extension = $nameExtension === 'jpeg' ? 'jpg' : $nameExtension;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            throw ValidationException::withMessages(['supporting_document' => ['Invalid attachment data.']]);
        }

        $maxBytes = 25 * 1024 * 1024;
        if (strlen($binary) > $maxBytes) {
            throw ValidationException::withMessages(['supporting_document' => ['Attachment must not exceed 25MB.']]);
        }

        $baseName = pathinfo($originalName ?? '', PATHINFO_FILENAME);
        $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName ?: 'supporting_document');
        $filename = date('YmdHis') . '_' . uniqid() . '_' . $baseName . '.' . $extension;
        $targetPath = 'images/accounting/manual-journal-supporting-docs/';

        if (!\File::exists(public_path($targetPath))) {
            \File::makeDirectory(public_path($targetPath), 0755, true);
        }

        \File::put(public_path($targetPath) . $filename, $binary);

        return $filename;
    }

    public function get() {
        return datatables()->of(JournalEntry::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
    }

    public function edit($id)
    {
        $journal_entries = JournalEntry::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('journal_entries'));
    }

    private function normalizeJournalDateInput($value, bool $nullable = false)
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return $nullable ? null : $raw;
        }

        $formats = ['m-d-Y', 'Y-m-d'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date && $date->format($format) === $raw) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
            }
        }

        throw ValidationException::withMessages([
            $nullable ? 'auto_reversing_date' : 'entry_date' => ['Date must be in MM-DD-YYYY format.'],
        ]);
    }

    public function status($id)
    {
        JournalEntry::find($id)->update(['status' => 'POSTED']);
        return "MANUAL JOURNAL ENTRY POSTED";
    }

    public function update(Request $request, $id)
    {
        JournalEntry::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            JournalEntry::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
