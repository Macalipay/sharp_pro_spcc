<?php

namespace App\Http\Controllers;
use Auth;

use App\EmployeeEducationalBackground;
use App\Traits\GlobalFunction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmployeeEducationalBackgroundController extends Controller
{
    use GlobalFunction;

    public function save(Request $request, $id) {
        if (empty($request->employee_id)) {
            return response()->json([
                'message' => 'Please save basic information first before adding educational background.',
                'errors' => ['employee_id' => ['Employee record is required.']],
            ], 422);
        }

        $validate = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'educational_attainment' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'school_year' => 'nullable|string|max:50',
            'school' => 'nullable|string|max:255',
            'attachment_data' => 'nullable|string',
            'attachment_name' => 'nullable|string|max:255',
            'attachment_mime' => 'nullable|string|max:100',
        ]);

        $attachment = $this->storeAttachment(
            $request->attachment_data,
            $request->attachment_name,
            'images/payroll/employee-attachments/educational-background/'
        );

        $payload = array_merge($validate, [
            'course' => $request->course ?? '',
            'school_year' => $request->school_year ?? '',
            'school' => $request->school ?? '',
            'attachment' => $attachment,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);

        $employment = EmployeeEducationalBackground::where('employee_id', $payload['employee_id'])
            ->where('school_year', $payload['school_year'])
            ->count();

        if($employment === 0) {
            EmployeeEducationalBackground::create($payload);
        }
        else {
            if (!$payload['attachment']) {
                $payload['attachment'] = EmployeeEducationalBackground::where('employee_id', $payload['employee_id'])
                    ->where('school_year', $payload['school_year'])
                    ->value('attachment');
            }

            EmployeeEducationalBackground::where('employee_id', $payload['employee_id'])
                ->where('school_year', $payload['school_year'])
                ->update([
                    'educational_attainment' => $payload['educational_attainment'],
                    'course' => $payload['course'],
                    'school_year' => $payload['school_year'],
                    'school' => $payload['school'],
                    'attachment' => $payload['attachment'],
                    'updated_by' => $payload['updated_by'],
                ]);
        }

        return response()->json(compact('validate'));
    }

    public function get($id) {
        if(request()->ajax()) {
            return datatables()->of(EmployeeEducationalBackground::where('employee_id', $id)->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            EmployeeEducationalBackground::find($item)->delete();
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
