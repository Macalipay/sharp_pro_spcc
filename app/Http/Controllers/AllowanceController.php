<?php

namespace App\Http\Controllers;

use Auth;
use App\ChartOfAccount;
use App\Allowance;
use App\AllowanceTagging;
use App\EmployeeInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AllowanceController extends Controller
{
    public function index()
    {
        $record = ChartOfAccount::orderBy('id', 'desc')->get();
        return view('backend.pages.setup.payroll_setup.allowance', compact('record'), ["type"=>"full-view"]);
    }
    
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Allowance::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'amount' => 'required'
        ]);
        
        if (!Allowance::where('name', $validatedData['name'])->exists()) {
            
            $request['workstation_id'] = Auth::user()->workstation_id;
            $request['created_by'] = Auth::user()->id;
            $request['updated_by'] = Auth::user()->id;
        
            Allowance::create($request->all());
        }
        else {
            return false;
        }
    }
    
    public function edit($id)
    {
        $allowance = Allowance::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('allowance'));
    }

    public function update(Request $request, $id)
    {
        $request['updated_by'] = Auth::user()->id;
        Allowance::find($id)->update($request->all());
        return "Record Saved";
    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Allowance::find($item)->delete();
        }
        
        return 'Record Deleted';
    }

    public function addAllowanceTag(Request $request) {
        $hasAutoReflectColumn = Schema::hasColumn('allowance_taggings', 'auto_reflect_in_payroll');
        $autoReflectValue = $request->has('auto_reflect_in_payroll')
            ? filter_var($request->auto_reflect_in_payroll, FILTER_VALIDATE_BOOLEAN)
            : true;

        if($request->action === "add") {
            $tag = AllowanceTagging::where('employee_id', $request->employee_id)
                ->where('allowance_id', $request->allowance_id)
                ->first();

            if ($tag) {
                $newAmount = $request->has('amount') ? $request->amount : $tag->amount;
                $payload = ['amount' => $newAmount];
                if ($hasAutoReflectColumn) {
                    $payload['auto_reflect_in_payroll'] = $autoReflectValue;
                }

                $tag->update($payload);
            } else {
                $payload = [
                    'employee_id' => $request->employee_id,
                    'allowance_id' => $request->allowance_id,
                    'amount' => $request->has('amount') ? $request->amount : 0,
                ];

                if ($hasAutoReflectColumn) {
                    $payload['auto_reflect_in_payroll'] = $autoReflectValue;
                }

                AllowanceTagging::create($payload);
            }
        } elseif ($request->action === "toggle_reflect" && $hasAutoReflectColumn) {
            $tag = AllowanceTagging::where('id', $request->id)
                ->where('employee_id', $request->employee_id)
                ->firstOrFail();

            $tag->update([
                'auto_reflect_in_payroll' => $autoReflectValue,
            ]);
        }
        else {
            if ($request->filled('id')) {
                AllowanceTagging::where('id', $request->id)->delete();
            } else {
                AllowanceTagging::where('employee_id', $request->employee_id)
                    ->where('allowance_id', $request->allowance_id)
                    ->delete();
            }
        }

        return response()->json(['success' => true]);
    }

    public function getAllowance(Request $request) {
        $employee = EmployeeInformation::select('id', 'employment_type')
            ->where('id', $request->employee_id)
            ->first();

        $payrollBasisCode = optional($employee)->employment_type;
        $payrollBasisLabel = $this->getPayrollBasisLabel($payrollBasisCode);

        $allowance = AllowanceTagging::with('allowances')
            ->where('employee_id', $request->employee_id)
            ->orderBy('id', 'desc')
            ->get();

        $allowance->transform(function ($item) use ($payrollBasisCode, $payrollBasisLabel) {
            $encodedAmount = round(floatval($item->amount ?? optional($item->allowances)->amount ?? 0), 2);
            $preview = $this->previewReflectedAllowance($encodedAmount, $payrollBasisCode);
            $autoReflect = array_key_exists('auto_reflect_in_payroll', $item->getAttributes())
                ? (bool) $item->auto_reflect_in_payroll
                : true;

            $item->encoded_amount = $encodedAmount;
            $item->payroll_basis = $payrollBasisLabel;
            $item->payroll_basis_code = $payrollBasisCode;
            $item->present_days_label = $preview['present_days_label'];
            $item->reflected_preview_amount = $preview['reflected_preview_amount'];
            $item->auto_reflect_in_payroll = $autoReflect;
            $item->reflected_preview_text = $autoReflect ? $preview['reflected_preview_text'] : 'Not reflected automatically';
            $item->reflected_formula = $autoReflect ? $preview['reflected_formula'] : 'Manual payroll allowance only';

            return $item;
        });

        return response()->json(compact('allowance'));
    }
    
    public function getAmount($id) {
        $allowance = Allowance::where('id', $id)->first();

        return response()->json(compact('allowance'));
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

    private function previewReflectedAllowance(float $encodedAmount, ?string $employmentType): array
    {
        if ($encodedAmount <= 0) {
            return [
                'reflected_preview_amount' => 0,
                'reflected_preview_text' => '₱0.00',
                'reflected_formula' => 'No allowance amount encoded',
                'present_days_label' => 'N/A',
            ];
        }

        switch ($employmentType) {
            case 'fixed_rate':
                $reflected = round($encodedAmount / 2, 2);
                return [
                    'reflected_preview_amount' => $reflected,
                    'reflected_preview_text' => '₱' . number_format($reflected, 2),
                    'reflected_formula' => 'Monthly allowance / 2 per cutoff',
                    'present_days_label' => 'N/A',
                ];

            case 'monthly_rate':
                return [
                    'reflected_preview_amount' => null,
                    'reflected_preview_text' => '(₱' . number_format($encodedAmount, 2) . ' / 26) x present days',
                    'reflected_formula' => 'Computed during payroll generation from approved present days',
                    'present_days_label' => 'Per payroll period',
                ];

            case 'daily_rate':
                return [
                    'reflected_preview_amount' => null,
                    'reflected_preview_text' => '₱' . number_format($encodedAmount, 2) . ' x present days',
                    'reflected_formula' => 'Computed during payroll generation from approved present days',
                    'present_days_label' => 'Per payroll period',
                ];

            default:
                return [
                    'reflected_preview_amount' => null,
                    'reflected_preview_text' => 'Payroll group not set',
                    'reflected_formula' => 'Set employee payroll group first',
                    'present_days_label' => 'N/A',
                ];
        }
    }
}
