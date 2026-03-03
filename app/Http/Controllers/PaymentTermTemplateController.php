<?php

namespace App\Http\Controllers;

use App\PaymentTermTemplate;
use Illuminate\Http\Request;
use Auth;

class PaymentTermTemplateController extends Controller
{
    public function index()
    {
        $payment_terms = PaymentTermTemplate::orderBy('id', 'desc')->get();
        return view('backend.pages.purchasing.maintenance.payment_terms', compact('payment_terms'), ["type" => "full-view"]);
    }

    public function get()
    {
        if (request()->ajax()) {
            return datatables()->of(PaymentTermTemplate::orderBy('id', 'desc')->get())
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'term_text' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id ?? null;
        $request['created_by'] = Auth::id();
        $request['updated_by'] = Auth::id();

        PaymentTermTemplate::create($request->all());

        return redirect()->back()->with('success', 'Successfully Added');
    }

    public function edit($id)
    {
        $payment_terms = PaymentTermTemplate::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('payment_terms'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'term_text' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $request['updated_by'] = Auth::id();
        PaymentTermTemplate::findOrFail($id)->update($request->all());

        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach ($record as $item) {
            $paymentTerm = PaymentTermTemplate::find($item);
            if ($paymentTerm) {
                $paymentTerm->delete();
            }
        }

        return 'Record Deleted';
    }

    public function options()
    {
        $payment_terms = PaymentTermTemplate::orderBy('term_text', 'asc')
            ->get(['id', 'term_text', 'description']);

        return response()->json(compact('payment_terms'));
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'term_text' => ['required', 'string'],
        ]);

        $term = PaymentTermTemplate::create([
            'term_text' => trim($request->term_text),
            'description' => $request->description,
            'workstation_id' => Auth::user()->workstation_id ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'ok',
            'payment_term' => $term,
        ]);
    }
}
