<?php

namespace App\Http\Controllers;

use App\AccountType;
use Illuminate\Http\Request;
use Auth;

class AccountTypeController extends Controller
{
    public function index()
    {
        $account_types = AccountType::orderBy('id', 'desc')->get();
        return view('backend.pages.accounting.maintenance.account_type', compact('account_types'), ["type"=>"full-view"]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => ['required'],
            'account_type' => ['required'],
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        AccountType::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(AccountType::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function edit($id)
    {
        $account_types = AccountType::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('account_types'));
    }

    public function update(Request $request, $id)
    {
        AccountType::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            AccountType::find($item)->delete();
        }

        return 'Record Deleted';
    }
}
