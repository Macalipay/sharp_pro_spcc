<?php

namespace App\Http\Controllers;

use App\DueDateTemplate;
use Illuminate\Http\Request;
use Auth;

class DueDateTemplateController extends Controller
{
    public function index()
    {
        $due_date_templates = DueDateTemplate::orderBy('id', 'desc')->get();
        return view('backend.pages.purchasing.maintenance.due_date_templates', compact('due_date_templates'), ["type" => "full-view"]);
    }

    public function get()
    {
        if (request()->ajax()) {
            return datatables()->of(DueDateTemplate::orderBy('id', 'desc')->get())
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

        DueDateTemplate::create($request->all());

        return redirect()->back()->with('success', 'Successfully Added');
    }

    public function edit($id)
    {
        $due_date_templates = DueDateTemplate::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('due_date_templates'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'template_text' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $request['updated_by'] = Auth::id();
        DueDateTemplate::findOrFail($id)->update($request->all());

        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach ($record as $item) {
            $dueDateTemplate = DueDateTemplate::find($item);
            if ($dueDateTemplate) {
                $dueDateTemplate->delete();
            }
        }

        return 'Record Deleted';
    }

    public function options()
    {
        $due_date_templates = DueDateTemplate::orderBy('template_text', 'asc')
            ->get(['id', 'template_text', 'description']);

        return response()->json(compact('due_date_templates'));
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'template_text' => ['required', 'string'],
        ]);

        $template = DueDateTemplate::create([
            'template_text' => trim($request->template_text),
            'description' => $request->description,
            'workstation_id' => Auth::user()->workstation_id ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'ok',
            'due_date_template' => $template,
        ]);
    }
}

