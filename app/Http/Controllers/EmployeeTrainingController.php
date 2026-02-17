<?php

namespace App\Http\Controllers;

use Auth;
use App\EmployeeTraining;
use App\Traits\GlobalFunction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmployeeTrainingController extends Controller
{
    use GlobalFunction;

    public function save(Request $request, $id) {
        $output = '';

        $validate = $request->validate([
            'training_no' => 'required',
            'training_name' => 'required',
            'training_provider' => 'required',
            'training_description' => 'required',
            'training_date' => 'required',
            'training_location' => 'required',
            'training_duration' => 'required',
            'training_outcome' => 'required',
            'training_type' => 'required',
            'expiration_date' => 'required',
            'attachment_data' => 'nullable|string',
            'attachment_name' => 'nullable|string|max:255',
            'attachment_mime' => 'nullable|string|max:100',

        ]);

        $attachment = $this->storeAttachment(
            $request->attachment_data,
            $request->attachment_name,
            'images/payroll/employee-attachments/training/'
        );

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
        $request['attachment'] = $attachment;

        $employment = EmployeeTraining::where('employee_id', $request->employee_id)->where('training_no', $request->training_no)->count();
        if($employment === 0) {
            $output = 'saved';
            EmployeeTraining::create($request->all());
        }
        else {
            $output = "updated";
            if (!$request->attachment) {
                $request['attachment'] = EmployeeTraining::where('employee_id', $request->employee_id)
                    ->where('training_no', $request->training_no)
                    ->value('attachment');
            }
            EmployeeTraining::where('employee_id', $request->employee_id)
                ->where('training_no', $request->training_no)
                ->update($request->except('_token', 'created_by'));
        }
        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeTraining::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeTraining::find($item)->delete();
        }

        return 'Record Deleted';
    }

    private function storeAttachment($encodedFile, $originalName, $targetPath)
    {
        if (!$encodedFile) {
            return null;
        }

        if (!preg_match('/^data:([^;]+);base64,(.+)$/', $encodedFile, $matches)) {
            throw ValidationException::withMessages(['attachment' => ['Invalid attachment format.']]);
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
                throw ValidationException::withMessages(['attachment' => ['Unsupported attachment type. Allowed: PDF, JPEG, PNG, DOC, DOCX.']]);
            }
            $extension = $nameExtension === 'jpeg' ? 'jpg' : $nameExtension;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            throw ValidationException::withMessages(['attachment' => ['Invalid attachment data.']]);
        }

        $maxBytes = 25 * 1024 * 1024;
        if (strlen($binary) > $maxBytes) {
            throw ValidationException::withMessages(['attachment' => ['Attachment must not exceed 25MB.']]);
        }

        $baseName = pathinfo($originalName ?? '', PATHINFO_FILENAME);
        $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName ?: 'attachment');
        $filename = date('YmdHis') . '_' . uniqid() . '_' . $baseName . '.' . $extension;

        if (!\File::exists(public_path($targetPath))) {
            \File::makeDirectory(public_path($targetPath), 0755, true);
        }

        \File::put(public_path($targetPath) . $filename, $binary);

        return $filename;
    }
}
