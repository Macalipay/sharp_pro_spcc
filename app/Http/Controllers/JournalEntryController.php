<?php

namespace App\Http\Controllers;

use App\JournalEntry;
use App\ChartOfAccount;
use Illuminate\Http\Request;
use Auth;

class JournalEntryController extends Controller
{
    public function index()
    {
        $journal_entries = JournalEntry::orderBy('id', 'desc')->get();
        $accounts = ChartOfAccount::orderBy('id')->get();

        return view('backend.pages.accounting.transaction.journal_entry', compact('journal_entries', 'accounts'), ["type"=>"full-view"]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date' => ['required'],
            'description' => ['required'],
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        JournalEntry::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(JournalEntry::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $journal_entries = JournalEntry::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('journal_entries'));
    }

    public function status($id)
    {
        JournalEntry::find($id)->update(['status' => 'POSTED']);
        return "JOURNAL ENTRY POSTED";
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
