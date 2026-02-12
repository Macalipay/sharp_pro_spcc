<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;

use App\City;
use App\Province;
use App\Barangay;

class GlobalController extends Controller
{
    public function series($prefix, $column_name, $model)
    {
        $model = "/App/" . $model;
        if(EmployeeInformation::count() === 0) {
            $request[$column_name] = $prefix.str_pad($model::count() + 1, 7, '0', STR_PAD_LEFT);
        }
        else {
            $request[$column_name] = $prefix.str_pad($model::count() + 1, 7, '0', STR_PAD_LEFT);
        }
    }

    public function getDateAndDays(Request $request) {
        
        $date = $request->date;
        $output = '';
        $dt = Carbon::parse($date);

        if($request->calendar_type === 'weekly') {
            $first_payment = $dt->endOfWeek()->format('Y-m-d');
            $start_of_week = 'monday';
            $end_of_week = 'sunday';
        }
        else if($request->calendar_type === 'twice_monthly') {
            if($dt->day <= 15) {
                $start_of_week = strtolower($dt->englishDayOfWeek);
                $first_payment = $dt->firstOfMonth()->addDays(14)->format('Y-m-d');
                $end_of_week = strtolower($dt->firstOfMonth()->addDays(14)->englishDayOfWeek);
            }
            else {
                $start_of_week = strtolower($dt->englishDayOfWeek);
                $first_payment = $dt->endOfMonth()->format('Y-m-d');
                $end_of_week = strtolower($dt->endOfMonth()->englishDayOfWeek);
            }
        }
        else if($request->calendar_type === 'monthly') {
            $start_of_week = strtolower($dt->subDays(1)->englishDayOfWeek);
            $first_payment = $dt->endOfMonth()->format('Y-m-d');
            $end_of_week = strtolower($dt->endOfMonth()->englishDayOfWeek);
        }

        return response()->json(compact('first_payment', 'start_of_week', 'end_of_week'));
    }

    public function get_province($region) {
        $province = Province::where('region_id', $region)->get();
        return response()->json(compact('province'));
    }

    public function get_city($province) {
        $city = City::where('province_id', $province)->get();
        return response()->json(compact('city'));
    }
    
    public function get_barangay($city) {
        $barangay = Barangay::where('city_id', $city)->get();
        return response()->json(compact('barangay'));
    }
}
