<?php

namespace App\Http\Controllers;

use App\DeliveryInstructionTemplate;
use Illuminate\Http\Request;
use Auth;

class DeliveryInstructionTemplateController extends Controller
{
    public function index()
    {
        $delivery_templates = DeliveryInstructionTemplate::orderBy('id', 'desc')->get();
        return view('backend.pages.purchasing.maintenance.delivery_templates', compact('delivery_templates'), ["type" => "full-view"]);
    }

    public function get()
    {
        if (request()->ajax()) {
            return datatables()->of(DeliveryInstructionTemplate::orderBy('id', 'desc')->get())
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_text' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $request['workstation_id'] = Auth::user()->workstation_id ?? null;
        $request['created_by'] = Auth::id();
        $request['updated_by'] = Auth::id();

        DeliveryInstructionTemplate::create($request->all());

        return redirect()->back()->with('success', 'Successfully Added');
    }

    public function edit($id)
    {
        $delivery_templates = DeliveryInstructionTemplate::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('delivery_templates'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'template_text' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $request['updated_by'] = Auth::id();
        DeliveryInstructionTemplate::findOrFail($id)->update($request->all());

        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach ($record as $item) {
            $template = DeliveryInstructionTemplate::find($item);
            if ($template) {
                $template->delete();
            }
        }

        return 'Record Deleted';
    }

    public function options()
    {
        $delivery_templates = DeliveryInstructionTemplate::orderBy('template_text', 'asc')
            ->get(['id', 'template_text', 'description']);

        return response()->json(compact('delivery_templates'));
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'template_text' => ['required', 'string'],
        ]);

        $template = DeliveryInstructionTemplate::create([
            'template_text' => trim($request->template_text),
            'description' => $request->description,
            'workstation_id' => Auth::user()->workstation_id ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'ok',
            'delivery_template' => $template,
        ]);
    }
}
