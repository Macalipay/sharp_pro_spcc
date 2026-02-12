<?php

namespace App\Http\Controllers;

use Auth;
use App\CreditNote;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function index()
    {
        return view('backend.pages.purchasing.transaction.credit_note', ["type"=>"full-view"]);
    }

    public function store(Request $request) {

        foreach($request->data as $item) {
            $item['workstation_id'] = Auth::user()->workstation_id;
            $item['created_by'] = Auth::user()->id;
            $item['updated_by'] = Auth::user()->id;

            CreditNote::create($item);
        }
        
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(CreditNote::with('project', 'po', 'po.supplier', 'chart')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function getCreditNote($id) {
        $credit = CreditNote::with('project', 'po', 'po.supplier', 'chart')->where('id', $id)->first();

        return response()->json(compact('credit'));
    }
}
