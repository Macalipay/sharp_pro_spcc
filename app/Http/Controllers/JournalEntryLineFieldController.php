<?php

namespace App\Http\Controllers;

use App\JournalEntryLineField;
use App\JournalEntry;
use App\ChartOfAccount;
use Illuminate\Http\Request;
use Auth;

class JournalEntryLineFieldController extends Controller
{
    public function store(Request $request)
    {
        $account = ChartOfAccount::find($request->chart_of_account_id);
        $sourceType = strtoupper((string) $request->get('data_type', 'MANUAL'));
        $allowedSystemSources = ['BILL', 'EXPENSE', 'PO', 'PURCHASE_ORDER'];
        if ($account && (string) $account->system_key === 'ACCOUNTS_PAYABLE_CONTROL' && !in_array($sourceType, $allowedSystemSources, true)) {
            return response()->json(['message' => 'Manual journal posting to system-locked Accounts Payable is not allowed.'], 422);
        }

        $request->validate([
            'journal_entry_id' => ['required'],
            'chart_of_account_id' => ['required'],
            'description' => ['required'],
            'debit_amount' => ['required'],
            'credit_amount' => ['required'],
            'tax_rate' => ['required'],
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        JournalEntryLineField::create($request->all());
        $this->JournalEntryComputation($request->journal_entry_id, $request->debit_amount, $request->credit_amount);
        return redirect()->back()->with('success','Successfully Added');
    }

    public function JournalEntryComputation($id, $debit, $credit)
    {
        $entry = JournalEntry::find($id);

        if ($entry) {
            $entry->total_debit += $debit;
            $entry->total_credit += $credit;
            $entry->save();
        }
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(JournalEntryLineField::with('journal_entry', 'chart_of_account')->where('journal_entry_id', $id)->orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $journal_entries = JournalEntryLineField::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('journal_entries'));
    }

    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::find($request->chart_of_account_id);
        $sourceType = strtoupper((string) $request->get('data_type', 'MANUAL'));
        $allowedSystemSources = ['BILL', 'EXPENSE', 'PO', 'PURCHASE_ORDER'];
        if ($account && (string) $account->system_key === 'ACCOUNTS_PAYABLE_CONTROL' && !in_array($sourceType, $allowedSystemSources, true)) {
            return response()->json(['message' => 'Manual journal posting to system-locked Accounts Payable is not allowed.'], 422);
        }

        JournalEntryLineField::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            JournalEntryLineField::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
