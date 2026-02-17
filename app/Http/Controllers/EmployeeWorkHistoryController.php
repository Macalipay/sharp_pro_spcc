<?php

namespace App\Http\Controllers;

use Auth;
use App\EmployeeWorkHistory;
use App\Traits\GlobalFunction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmployeeWorkHistoryController extends Controller
{
    use GlobalFunction;

    public function save(Request $request, $id) {
        $output = '';

        $validate = $request->validate([
            'company' => 'required',
            'position' => 'required',
            'date_hired' => 'required',
            'date_of_resignation' => 'required',
            'remarks' => 'required',
            'attachment_data' => 'nullable|string',
            'attachment_name' => 'nullable|string|max:255',
            'attachment_mime' => 'nullable|string|max:100',
        ]);

        $attachment = $this->storeAttachment(
            $request->attachment_data,
            $request->attachment_name,
            'images/payroll/employee-attachments/work-history/'
        );

        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;
        $request['attachment'] = $attachment;

        $employment = EmployeeWorkHistory::where('employee_id', $request->employee_id)->where('date_hired', $request->date_hired)->where('company', $request->company)->count();
        if($employment === 0) {
            $output = 'saved';
            EmployeeWorkHistory::create($request->all());
        }
        else {
            $output = "updated";
            if (!$request->attachment) {
                $request['attachment'] = EmployeeWorkHistory::where('employee_id', $request->employee_id)
                    ->where('date_hired', $request->date_hired)
                    ->where('company', $request->company)
                    ->value('attachment');
            }
            EmployeeWorkHistory::where('employee_id', $request->employee_id)
                ->where('date_hired', $request->date_hired)
                ->where('company', $request->company)
                ->update($request->except('_token', 'created_by'));
        }
        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeWorkHistory::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeWorkHistory::find($item)->delete();
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
