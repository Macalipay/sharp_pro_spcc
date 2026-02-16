<?php

namespace App\Http\Controllers;

use Auth;
use App\WorkCalendar;
use App\WorkCalendarPreset;
use Illuminate\Http\Request;

class WorkCalendarController extends Controller
{
    public function save(Request $request, $id) {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'sunday_start_time' => 'nullable',
            'sunday_end_time' => 'nullable',
            'monday_start_time' => 'nullable',
            'monday_end_time' => 'nullable',
            'tuesday_start_time' => 'nullable',
            'tuesday_end_time' => 'nullable',
            'wednesday_start_time' => 'nullable',
            'wednesday_end_time' => 'nullable',
            'thursday_start_time' => 'nullable',
            'thursday_end_time' => 'nullable',
            'friday_start_time' => 'nullable',
            'friday_end_time' => 'nullable',
            'saturday_start_time' => 'nullable',
            'saturday_end_time' => 'nullable',
            'is_flexi_time' => 'nullable|boolean',
        ]);

        $payload = array_merge($validated, [
            'updated_by' => Auth::user()->id,
        ]);

        if (WorkCalendar::where('employee_id', $validated['employee_id'])->count() === 0) {
            $payload['created_by'] = Auth::user()->id;
            WorkCalendar::create($payload);
        } else {
            WorkCalendar::where('employee_id', $validated['employee_id'])->update($payload);
        }

        return response()->json();
    }

    public function getPresets()
    {
        $presets = WorkCalendarPreset::where('workstation_id', Auth::user()->workstation_id)
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['presets' => $presets]);
    }

    public function savePreset(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'selected_days' => 'required|array|min:1',
            'selected_days.*' => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'time_in' => 'required',
            'time_off' => 'required',
            'start_day' => 'nullable|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'nullable',
            'end_day' => 'nullable|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'end_time' => 'nullable',
            'is_flexi_time' => 'nullable|boolean',
        ]);

        $orderedDays = array_values(array_intersect([
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        ], array_map('strtolower', $validated['selected_days'])));

        $startDay = $validated['start_day'] ?? ($orderedDays[0] ?? 'monday');
        $endDay = $validated['end_day'] ?? ($orderedDays[count($orderedDays) - 1] ?? 'monday');
        $timeIn = $validated['time_in'];
        $timeOff = $validated['time_off'];

        $preset = WorkCalendarPreset::create([
            'name' => strtoupper(trim($validated['name'])),
            'selected_days' => json_encode($orderedDays),
            'time_in' => $timeIn,
            'time_off' => $timeOff,
            'is_flexi_time' => !empty($validated['is_flexi_time']) ? 1 : 0,
            'start_day' => strtolower($startDay),
            'start_time' => $timeIn,
            'end_day' => strtolower($endDay),
            'end_time' => $timeOff,
            'workstation_id' => Auth::user()->workstation_id,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);

        return response()->json([
            'message' => 'Work calendar preset saved.',
            'preset' => $preset,
        ]);
    }
}
