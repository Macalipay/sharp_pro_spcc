<?php

namespace App\Http\Controllers;

use App\Payroll;
use App\PayrollCalendarHeaders;
use App\PayrollCalendarDetails;
use Auth;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index() {
        return view('backend.pages.transaction.payroll.index', ["type"=>"full-view"],);
    }

    public function payrun() {
        return view('backend.pages.payroll.transaction.payrun.index');
    }

    public function calendar() {
        return view('backend.pages.payroll.maintenance.payroll_calendar');
    }

}
