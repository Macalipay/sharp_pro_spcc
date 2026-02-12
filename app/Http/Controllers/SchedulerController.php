<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchedulerController extends Controller
{
    public function index()
    {
        return view('backend.pages.transaction.timekeeping.scheduling', ["type"=>"full-view"]);
    }
}
