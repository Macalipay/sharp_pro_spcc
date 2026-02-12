<?php

namespace App\Http\Controllers;

use App\Traits\GlobalFunction;
use Auth;
use App\PersonnelFile;
use Illuminate\Http\Request;

class PersonnelFileController extends Controller
{
    use GlobalFunction;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $personnel = PersonnelFile::orderBy('id', 'desc')->get();
        return view('backend.pages.payroll.transaction.201_file.index', compact('personnel'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $validate = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'firstname' => 'required',
            'gender' => 'required',
            'birthdate' => 'required',
            'citizenship' => 'required',
            'phone1' => 'required',
            'street_1' => 'required',
            'barangay_1' => 'required',
            'city_1' => 'required',
            'province_1' => 'required',
            'country_1' => 'required',
            'zip_1' => 'required',
            'email' => 'required|unique:employees'
        ]);


        $request['employee_no'] = $this->series('EMP', 'PersonnelFile');

        if($request->profile_img !== null) {
            $request['profile_img'] = $this->uploadFile($request->profile_img, 'images/payroll/201-file/', date('Ymdhis'));
        }
        else {
            $request['profile_img'] = "default.png";
        }
 
        $request['workstation_id'] = Auth::user()->workstation_id;
        $request['created_by'] = Auth::user()->id;
        $request['updated_by'] = Auth::user()->id;

        $employee = PersonnelFile::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PersonnelFile  $personnelFile
     * @return \Illuminate\Http\Response
     */
    public function show(PersonnelFile $personnelFile)
    {
        //
    }

    public function get()
    {
        if(request()->ajax()) {
            return datatables()->of(PersonnelFile::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PersonnelFile  $personnelFile
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $personnel = PersonnelFile::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('personnel'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PersonnelFile  $personnelFile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'firstname' => 'required',
            'gender' => 'required',
            'birthdate' => 'required',
            'citizenship' => 'required',
            'phone1' => 'required',
            'street_1' => 'required',
            'barangay_1' => 'required',
            'city_1' => 'required',
            'province_1' => 'required',
            'country_1' => 'required',
            'zip_1' => 'required',
            'email' => 'required'
        ]);

        if($request->profile_img !== null && $request->profile_img !== '') {
            $request['profile_img'] = $this->uploadFile($request->profile_img, 'images/payroll/201-file/', date('Ymdhis'));
        }
        else {
            $request['profile_img'] = PersonnelFile::where('id', $id)->first()->profile_img;
        }

        PersonnelFile::findOrFail($id)->update($request->except('created_by'));
        return response()->json(compact('validate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PersonnelFile  $personnelFile
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            PersonnelFile::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
