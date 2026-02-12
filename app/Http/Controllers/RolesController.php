<?php

namespace App\Http\Controllers;

use App\Roles;
use Illuminate\Http\Request;
use Auth;

class RolesController extends Controller
{
    public function index()
    {
        return view('backend.pages.setup.role', ["type"=>"full-view"]);
    }

    public function get() {
        if(request()->ajax()) {
            return datatables()->of(Roles::orderBy('id', 'desc')->get())
            ->addIndexColumn()
            ->make(true);
        }
    }

    public function store(Request $request)
    {
        $department = $request->validate([
            'name' => ['required'],
        ]);

        $request->request->add(['guard_name' => 'web']);
        Roles::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function edit($id)
    {
        $roles = Roles::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('roles'));
    }

    public function update(Request $request, $id)
    {
        $request['status'] = isset($request['status'])?1:0;
        Roles::find($id)->update($request->all());
        return response()->json(['Successfully Updated']);
    }
    
    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            Roles::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
