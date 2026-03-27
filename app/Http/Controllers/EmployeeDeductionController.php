<?php

namespace App\Http\Controllers;

use App\Deductions;
use App\EmployeeDeduction;
use App\EmployeeDeductionTransaction;
use App\EmployeeInformation;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeDeductionController extends Controller
{
    public function get($employeeId)
    {
        $employee = EmployeeInformation::select('id', 'employment_type')
            ->where('id', $employeeId)
            ->firstOrFail();

        $records = EmployeeDeduction::with(['deduction'])
            ->where('employee_id', $employeeId)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'paused' THEN 1 ELSE 2 END")
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) use ($employee) {
                $preview = $this->buildScheduledPreview($item);
                return $this->transformRecord($item, $employee->employment_type, $preview);
            })
            ->values();

        $summary = [
            'active_deductions' => $records->where('status', 'active')->count(),
            'total_balance' => round($records->sum('remaining_balance'), 2),
            'total_paid' => round($records->sum('total_paid'), 2),
            'upcoming_auto_deduction' => round(
                $records
                    ->where('status', 'active')
                    ->where('auto_deduct_in_payroll', true)
                    ->sum('scheduled_preview_amount'),
                2
            ),
        ];

        return response()->json([
            'summary' => $summary,
            'deductions' => $records,
        ]);
    }

    public function history($id)
    {
        $record = EmployeeDeduction::with(['deduction', 'transactions' => function ($query) {
            $query->orderBy('processed_date', 'desc')->orderBy('id', 'desc');
        }])->findOrFail($id);

        $employee = EmployeeInformation::select('id', 'employment_type')
            ->where('id', $record->employee_id)
            ->first();

        $preview = $this->buildScheduledPreview($record);
        $transformed = $this->transformRecord($record, optional($employee)->employment_type, $preview);

        $history = $record->transactions->map(function ($item) {
            return [
                'id' => $item->id,
                'sequence_no' => $item->sequence_no ?: '-',
                'payroll_period' => $item->payroll_period_start && $item->payroll_period_end
                    ? Carbon::parse($item->payroll_period_start)->format('M d, Y') . ' - ' . Carbon::parse($item->payroll_period_end)->format('M d, Y')
                    : '-',
                'processed_date' => $item->processed_date ? Carbon::parse($item->processed_date)->format('M d, Y') : '-',
                'deduction_type' => optional($item->deduction)->name ?: 'Deduction',
                'reference' => $item->reference_name ?: '-',
                'scheduled_amount' => round(floatval($item->scheduled_amount), 2),
                'actual_deducted_amount' => round(floatval($item->actual_deducted_amount), 2),
                'running_balance' => round(floatval($item->running_balance), 2),
                'source' => $this->sourceLabel($item->source),
                'payroll_reference_no' => $item->payroll_reference_no ?: '-',
                'status' => ucwords(str_replace('_', ' ', $item->status ?: 'posted')),
                'notes' => $item->notes ?: '-',
            ];
        })->values();

        return response()->json([
            'record' => $transformed,
            'history' => $history,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'deduction_id' => ['required', 'integer', 'exists:deductions,id'],
            'reference_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'integer', 'min:1'],
            'deduction_per_payroll' => ['nullable', 'numeric', 'min:0'],
            'deduction_frequency' => ['required', 'string', 'max:50'],
            'effective_start_payroll' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:effective_start_payroll'],
            'auto_deduct_in_payroll' => ['nullable', 'boolean'],
            'stop_when_fully_paid' => ['nullable', 'boolean'],
            'allow_manual_override' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,active,paused,completed'],
            'id' => ['nullable', 'integer', 'exists:employee_deductions,id'],
        ]);

        $record = !empty($validated['id'])
            ? EmployeeDeduction::where('employee_id', $validated['employee_id'])->findOrFail($validated['id'])
            : new EmployeeDeduction();

        $normalized = $this->normalizeDeductionAmounts(
            floatval($validated['total_amount']),
            isset($validated['payment_terms']) ? intval($validated['payment_terms']) : null,
            isset($validated['deduction_per_payroll']) ? floatval($validated['deduction_per_payroll']) : null
        );

        $totalPaid = $record->exists ? round(floatval($record->total_paid), 2) : 0;
        $remainingBalance = max(0, round($normalized['total_amount'] - $totalPaid, 2));
        $status = $validated['status'];

        if ($remainingBalance <= 0) {
            $status = 'completed';
        }

        $record->fill([
            'employee_id' => $validated['employee_id'],
            'deduction_id' => $validated['deduction_id'],
            'reference_name' => $validated['reference_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'total_amount' => $normalized['total_amount'],
            'payment_terms' => $normalized['payment_terms'],
            'deduction_per_payroll' => $normalized['deduction_per_payroll'],
            'deduction_frequency' => $validated['deduction_frequency'],
            'effective_start_payroll' => $validated['effective_start_payroll'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'auto_deduct_in_payroll' => filter_var($request->input('auto_deduct_in_payroll', true), FILTER_VALIDATE_BOOLEAN),
            'stop_when_fully_paid' => filter_var($request->input('stop_when_fully_paid', true), FILTER_VALIDATE_BOOLEAN),
            'allow_manual_override' => filter_var($request->input('allow_manual_override', false), FILTER_VALIDATE_BOOLEAN),
            'status' => $status,
            'total_paid' => $totalPaid,
            'remaining_balance' => $remainingBalance,
            'workstation_id' => Auth::user()->workstation_id ?? null,
            'updated_by' => Auth::user()->id ?? null,
        ]);

        if (!$record->exists) {
            $record->created_by = Auth::user()->id ?? null;
        }

        $record->save();

        return response()->json([
            'message' => 'Deduction saved successfully.',
            'id' => $record->id,
        ]);
    }

    public function status(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:employee_deductions,id'],
            'action' => ['required', 'in:pause,resume,complete'],
        ]);

        $record = EmployeeDeduction::findOrFail($validated['id']);

        if ($validated['action'] === 'pause') {
            $record->status = 'paused';
        } elseif ($validated['action'] === 'resume') {
            $record->status = $record->remaining_balance > 0 ? 'active' : 'completed';
        } elseif ($validated['action'] === 'complete') {
            $record->status = 'completed';
            $record->remaining_balance = 0;
            $record->total_paid = round(floatval($record->total_amount), 2);
        }

        $record->updated_by = Auth::user()->id ?? null;
        $record->save();

        return response()->json(['message' => 'Deduction status updated.']);
    }

    public function manualTransaction(Request $request)
    {
        $validated = $request->validate([
            'employee_deduction_id' => ['required', 'integer', 'exists:employee_deductions,id'],
            'source' => ['required', 'in:manual_payment,manual_adjustment,reversal'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'processed_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $record = EmployeeDeduction::with('deduction')->findOrFail($validated['employee_deduction_id']);
        $amount = round(floatval($validated['amount']), 2);
        $totalPaid = round(floatval($record->total_paid), 2);
        $remaining = round(floatval($record->remaining_balance), 2);

        if ($validated['source'] === 'reversal') {
            $actualAmount = -1 * min($amount, $totalPaid);
            $newTotalPaid = max(0, round($totalPaid + $actualAmount, 2));
            $newRemaining = min(round(floatval($record->total_amount), 2), round($remaining - $actualAmount, 2));
        } else {
            $actualAmount = min($amount, $remaining);
            $newTotalPaid = round($totalPaid + $actualAmount, 2);
            $newRemaining = max(0, round($remaining - $actualAmount, 2));
        }

        $record->total_paid = $newTotalPaid;
        $record->remaining_balance = $newRemaining;
        if ($newRemaining <= 0 && $record->stop_when_fully_paid) {
            $record->status = 'completed';
        } elseif ($record->status === 'completed' && $newRemaining > 0) {
            $record->status = 'active';
        }
        $record->updated_by = Auth::user()->id ?? null;
        $record->save();

        EmployeeDeductionTransaction::create([
            'employee_deduction_id' => $record->id,
            'employee_id' => $record->employee_id,
            'summary_id' => null,
            'sequence_no' => null,
            'deduction_id' => $record->deduction_id,
            'payroll_period_start' => null,
            'payroll_period_end' => null,
            'processed_date' => $validated['processed_date'] ?? Carbon::today()->toDateString(),
            'reference_name' => $record->reference_name,
            'scheduled_amount' => $amount,
            'actual_deducted_amount' => $actualAmount,
            'running_balance' => $newRemaining,
            'source' => $validated['source'],
            'notes' => $validated['notes'] ?? null,
            'payroll_reference_no' => null,
            'status' => 'posted',
            'workstation_id' => Auth::user()->workstation_id ?? null,
            'created_by' => Auth::user()->id ?? null,
            'updated_by' => Auth::user()->id ?? null,
        ]);

        return response()->json(['message' => 'Deduction transaction saved successfully.']);
    }

    private function transformRecord(EmployeeDeduction $item, ?string $employmentType, array $preview): array
    {
        return [
            'id' => $item->id,
            'deduction_id' => $item->deduction_id,
            'deduction_type' => optional($item->deduction)->name ?: 'Deduction',
            'reference_name' => $item->reference_name ?: '',
            'description' => $item->description ?: '',
            'total_amount' => round(floatval($item->total_amount), 2),
            'payment_terms' => $item->payment_terms,
            'deduction_per_payroll' => round(floatval($item->deduction_per_payroll), 2),
            'deduction_frequency' => $item->deduction_frequency,
            'effective_start_payroll' => optional($item->effective_start_payroll)->format('Y-m-d'),
            'end_date' => optional($item->end_date)->format('Y-m-d'),
            'auto_deduct_in_payroll' => (bool) $item->auto_deduct_in_payroll,
            'stop_when_fully_paid' => (bool) $item->stop_when_fully_paid,
            'allow_manual_override' => (bool) $item->allow_manual_override,
            'status' => $item->status,
            'status_label' => ucwords(str_replace('_', ' ', $item->status)),
            'total_paid' => round(floatval($item->total_paid), 2),
            'remaining_balance' => round(floatval($item->remaining_balance), 2),
            'payroll_basis' => $this->getPayrollBasisLabel($employmentType),
            'scheduled_preview_amount' => $preview['amount'],
            'scheduled_preview_formula' => $preview['formula'],
        ];
    }

    private function normalizeDeductionAmounts(float $totalAmount, ?int $paymentTerms, ?float $deductionPerPayroll): array
    {
        $totalAmount = round(max($totalAmount, 0), 2);
        $paymentTerms = $paymentTerms && $paymentTerms > 0 ? $paymentTerms : null;
        $deductionPerPayroll = $deductionPerPayroll !== null ? round(max($deductionPerPayroll, 0), 2) : 0;

        if ($paymentTerms && $deductionPerPayroll <= 0) {
            $deductionPerPayroll = round($totalAmount / max($paymentTerms, 1), 2);
        }

        if (!$paymentTerms && $deductionPerPayroll > 0) {
            $paymentTerms = max(1, (int) ceil($totalAmount / max($deductionPerPayroll, 0.01)));
        }

        if (!$paymentTerms) {
            $paymentTerms = 1;
        }

        if ($deductionPerPayroll <= 0) {
            $deductionPerPayroll = $totalAmount;
        }

        return [
            'total_amount' => $totalAmount,
            'payment_terms' => $paymentTerms,
            'deduction_per_payroll' => round($deductionPerPayroll, 2),
        ];
    }

    private function buildScheduledPreview(EmployeeDeduction $item): array
    {
        $remaining = round(floatval($item->remaining_balance), 2);
        $perPayroll = round(floatval($item->deduction_per_payroll), 2);

        if ($remaining <= 0) {
            return [
                'amount' => 0,
                'formula' => 'Completed deduction',
            ];
        }

        if ($perPayroll <= 0 && intval($item->payment_terms) > 0) {
            $perPayroll = round(floatval($item->total_amount) / max(intval($item->payment_terms), 1), 2);
        }

        if ($perPayroll <= 0) {
            $perPayroll = $remaining;
        }

        $scheduled = min($remaining, $perPayroll);

        return [
            'amount' => round($scheduled, 2),
            'formula' => intval($item->payment_terms) > 1
                ? 'Per payroll deduction based on terms'
                : 'Per payroll deduction amount',
        ];
    }

    private function getPayrollBasisLabel(?string $employmentType): string
    {
        switch ($employmentType) {
            case 'fixed_rate':
                return 'Fixed Rate';
            case 'monthly_rate':
                return 'Monthly';
            case 'daily_rate':
                return 'Daily';
            default:
                return 'Not Set';
        }
    }

    private function sourceLabel(?string $source): string
    {
        switch ($source) {
            case 'auto_payroll':
                return 'Auto Payroll';
            case 'manual_adjustment':
                return 'Manual Adjustment';
            case 'manual_payment':
                return 'Manual Payment';
            case 'reversal':
                return 'Reversal';
            default:
                return ucwords(str_replace('_', ' ', (string) $source));
        }
    }
}
