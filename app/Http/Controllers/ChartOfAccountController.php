<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\AccountType;
use Illuminate\Http\Request;
use Auth;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $chart_of_accounts = ChartOfAccount::orderBy('id', 'desc')->get();
        $account_types = AccountType::get();
        return view('backend.pages.accounting.maintenance.chart_of_account', compact('chart_of_accounts', 'account_types'), ["type"=>"full-view"]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_number' => ['required'],
            'account_name' => ['required'],
            'account_type' => ['required'],
            'description' => ['required'],
            'normal_balance' => ['required'],
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        ChartOfAccount::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(ChartOfAccount::with('account_type')->orderBy('id', 'desc')->get())
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
        ChartOfAccount::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            ChartOfAccount::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
