<?php

namespace App\Http\Controllers;

use App\EmployeeInformation;
use App\MaterialsRequisitionForm;
use App\MaterialsRequisitionFormDetail;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class MaterialsRequisitionFormController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('project_name', 'asc')->get();
        $employees = EmployeeInformation::orderBy('lastname', 'asc')->get();

        return view(
            'backend.pages.purchasing.transaction.material_requisition_form',
            compact('projects', 'employees'),
            ["type" => "full-view"]
        );
    }

    public function get()
    {
        if (request()->ajax()) {
            return datatables()->of(
                MaterialsRequisitionForm::with('project', 'requestedBy', 'notedBy', 'approvedBy', 'details')->orderBy('id', 'desc')->get()
            )
                ->addColumn('project_name', function ($row) {
                    return optional($row->project)->project_name ?: '-';
                })
                ->addColumn('requested_by_name', function ($row) {
                    return optional($row->requestedBy)->firstname
                        ? trim($row->requestedBy->firstname . ' ' . $row->requestedBy->lastname)
                        : '-';
                })
                ->addColumn('noted_by_name', function ($row) {
                    return optional($row->notedBy)->firstname
                        ? trim($row->notedBy->firstname . ' ' . $row->notedBy->lastname)
                        : '-';
                })
                ->addColumn('approved_by_name', function ($row) {
                    return optional($row->approvedBy)->firstname
                        ? trim($row->approvedBy->firstname . ' ' . $row->approvedBy->lastname)
                        : '-';
                })
                ->addColumn('details_count', function ($row) {
                    return $row->details ? $row->details->count() : 0;
                })
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'mrf_no' => ['required', 'string'],
            'project_id' => ['nullable', 'integer'],
            'location' => ['nullable', 'string'],
            'requested_by' => ['nullable', 'integer'],
            'noted_by' => ['nullable', 'integer'],
            'approved_by' => ['nullable', 'integer'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.quantity' => ['nullable', 'numeric'],
            'details.*.unit' => ['nullable', 'string'],
            'details.*.particulars' => ['nullable', 'string'],
            'details.*.location_to_be_used' => ['nullable', 'string'],
            'details.*.date_required' => ['nullable', 'date'],
            'details.*.approved_quantity' => ['nullable', 'numeric'],
            'details.*.remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request) {
            $header = MaterialsRequisitionForm::create([
                'date' => $request->date,
                'mrf_no' => $request->mrf_no,
                'project_id' => $request->project_id,
                'location' => $request->location,
                'requested_by' => $request->requested_by,
                'noted_by' => $request->noted_by,
                'approved_by' => $request->approved_by,
                'workstation_id' => Auth::user()->workstation_id ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ((array) $request->details as $detail) {
                MaterialsRequisitionFormDetail::create([
                    'materials_requisition_form_id' => $header->id,
                    'quantity' => $detail['quantity'] ?? null,
                    'unit' => $detail['unit'] ?? null,
                    'particulars' => $detail['particulars'] ?? null,
                    'location_to_be_used' => $detail['location_to_be_used'] ?? null,
                    'date_required' => $detail['date_required'] ?? null,
                    'approved_quantity' => $detail['approved_quantity'] ?? null,
                    'remarks' => $detail['remarks'] ?? null,
                    'workstation_id' => Auth::user()->workstation_id ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Successfully Added');
    }

    public function edit($id)
    {
        $materials_requisition_forms = MaterialsRequisitionForm::with('details')->where('id', $id)->firstOrFail();
        return response()->json(compact('materials_requisition_forms'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'mrf_no' => ['required', 'string'],
            'project_id' => ['nullable', 'integer'],
            'location' => ['nullable', 'string'],
            'requested_by' => ['nullable', 'integer'],
            'noted_by' => ['nullable', 'integer'],
            'approved_by' => ['nullable', 'integer'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.quantity' => ['nullable', 'numeric'],
            'details.*.unit' => ['nullable', 'string'],
            'details.*.particulars' => ['nullable', 'string'],
            'details.*.location_to_be_used' => ['nullable', 'string'],
            'details.*.date_required' => ['nullable', 'date'],
            'details.*.approved_quantity' => ['nullable', 'numeric'],
            'details.*.remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $id) {
            MaterialsRequisitionForm::findOrFail($id)->update([
                'date' => $request->date,
                'mrf_no' => $request->mrf_no,
                'project_id' => $request->project_id,
                'location' => $request->location,
                'requested_by' => $request->requested_by,
                'noted_by' => $request->noted_by,
                'approved_by' => $request->approved_by,
                'updated_by' => Auth::id(),
            ]);

            MaterialsRequisitionFormDetail::where('materials_requisition_form_id', $id)->delete();

            foreach ((array) $request->details as $detail) {
                MaterialsRequisitionFormDetail::create([
                    'materials_requisition_form_id' => $id,
                    'quantity' => $detail['quantity'] ?? null,
                    'unit' => $detail['unit'] ?? null,
                    'particulars' => $detail['particulars'] ?? null,
                    'location_to_be_used' => $detail['location_to_be_used'] ?? null,
                    'date_required' => $detail['date_required'] ?? null,
                    'approved_quantity' => $detail['approved_quantity'] ?? null,
                    'remarks' => $detail['remarks'] ?? null,
                    'workstation_id' => Auth::user()->workstation_id ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        });

        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach ($record as $item) {
            $mrf = MaterialsRequisitionForm::find($item);
            if ($mrf) {
                MaterialsRequisitionFormDetail::where('materials_requisition_form_id', $mrf->id)->delete();
                $mrf->delete();
            }
        }

        return 'Record Deleted';
    }
}
