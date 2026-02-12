<?php

namespace App\Http\Controllers;

use App\ModelHasRoles;
use App\User;
use Illuminate\Http\Request;

class ModelHasRolesController extends Controller
{
    public function get($id) {
        $record = ModelHasRoles::where('model_id', $id)->first();

        return response()->json(compact('record'));
    }

    public function store(Request $request)
    {
        $user = $request->validate([
            'role_id' => ['required']
        ]);

        $request->request->add(['model_type' => 'App\User']);

        ModelHasRoles::create($request->all());

        return redirect()->back()->with('success','Successfully Added');
    }

    public function edit($id)
    {
        $record = ModelHasRoles::where('id', $id)->orderBy('id')->firstOrFail();
        return response()->json(compact('record'));
    }

    public function update(Request $request, $id)
    {
        if ($request->has('po')) {
            $poData = is_array($request->po) ? json_encode($request->po) : $request->po;
            User::where('id', $id)->update(['po' => $poData]);
        }
        $roleData = $request->except(['_token', 'po']);
        if (!empty($roleData)) {
            ModelHasRoles::where('model_id', $id)->update($roleData);
        }

        return response()->json(['message' => 'Successfully Updated']);

    }

    public function destroy(Request $request)
    {
        $record = $request->data;

        foreach($record as $item) {
            ModelHasRoles::find($item)->delete();
        }
        
        return 'Record Deleted';
    }
}
