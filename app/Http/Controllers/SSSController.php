<?php

namespace App\Http\Controllers;

use Auth;
use App\SSS;
use App\Classes\Computation\Payroll\SSS as SSSTable;
use Illuminate\Http\Request;

class SSSController extends Controller
{
    public function index() {
        return view('backend.pages.payroll.maintenance.sss', ["type"=>"2-view"]);
    }
    public function get() {
        if(request()->ajax()) {
            return datatables()->of(
                SSS::orderBy('id', 'asc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function generateSSSTable(Request $request) {
        $sss = new SSSTable();

        $data = array();

        $row = $request->row;

        $start_range = $request->range_1;
        $end_range = $request->range_2;
        
        $monthly_minimum = $request->monthly_minimum;

        $regular_ss = $request->regular_ss;
        $ee_percentage = ($request->ee)/100;
        $er_percentage = ($request->er)/100;
        
        $range_interval = $request->range_interval;
        $monthly_interval = $request->monthly_interval;
        $regular_interval = $request->regular_interval;

        $ee = $regular_ss * $ee_percentage;
        $er = $regular_ss * $er_percentage;

        array_push($data, array(
            "range_1"=>$start_range,
            "range_2"=>$end_range,
            "regular_ss"=>$monthly_minimum,
            "wisp"=>0,
            "ee"=>$ee,
            "er"=>$er,
            "ecc"=>0,
            "wisp_ee"=>0,
            "wisp_er"=>0,
            "workstation_id" => Auth::user()->workstation_id,
            "created_by" => Auth::user()->id,
            "updated_by" => Auth::user()->id
        ));
        
        for($i = 0; $i < $row; $i++) {
            $start_range = number_format((float)round($end_range), 2, '.', '');
            $end_range = $sss->getLastRange($end_range, $range_interval);
            $monthly_minimum = $monthly_minimum !== 20000?$monthly_minimum + $monthly_interval:$monthly_minimum;
            $regular_ss = $regular_ss !== 2800?$regular_ss + $regular_interval:$regular_ss;
            
            $ee = $regular_ss * $ee_percentage;
            $er = $regular_ss * $er_percentage;
            
            array_push($data, array(
                "range_1"=>$start_range,
                "range_2"=>$end_range,
                "regular_ss"=>$monthly_minimum,
                "wisp"=>0,
                "ee"=>$ee,
                "er"=>$er,
                "ecc"=>0,
                "wisp_ee"=>0,
                "wisp_er"=>0,
                "workstation_id" => Auth::user()->workstation_id,
                "created_by" => Auth::user()->id,
                "updated_by" => Auth::user()->id
            ));
        }

        SSS::truncate();
        SSS::insert($data);

        return $data;
    }
}
