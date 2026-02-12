<?php

namespace App\Http\Controllers;

use Auth;

use App\Roles;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request){
        if ($request->ajax()) {
            $role = Roles::find($request->role_id);

                if ($role) {
                    $permissions = $role->permissions->sortBy([
                            ['category', 'asc'],
                            ['sub_category', 'asc'],
                        ])->map(function ($permission) use ($role) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'category' => $permission->category,
                            'sub_category' => $permission->sub_category,
                            'actions' => [
                                'view' => $role->hasPermissionTo('view_' . $permission->name),
                                'add' => $role->hasPermissionTo('add_' . $permission->name),
                                'edit' => $role->hasPermissionTo('edit_' . $permission->name),
                                'delete' => $role->hasPermissionTo('delete_' . $permission->name),
                                'print' => $role->hasPermissionTo('print_' . $permission->name),
                                'download' => $role->hasPermissionTo('download_' . $permission->name),
                            ],
                        ];
                    })->groupBy(['category', 'sub_category'])
                    ->toArray();

                    return response()->json(['permissions' => $permissions]);
                }
            return response()->json(['error' => 'Role not found'], 404);
        }

        $roles = Roles::with('permissions')->get();
        $permissionsList = Permission::all();

        return view('backend.pages.setup.permission', compact('roles', 'permissionsList'), ["type"=>"full-view"]);
    }


    public function updatePermissions(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array', 
        ]);

        $role = Roles::find($validated['role_id']);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $checkedPermissions = collect($request->permissions)
            ->filter(function ($value) {
                return $value != "0";
            })
            ->keys() 
            ->toArray();

        $role->syncPermissions(Permission::whereIn('id', $checkedPermissions)->pluck('name')->toArray());

        return response()->json(['success' => 'Permissions updated successfully']);
    }

    public function getPermissions(Request $request){
        $role = Roles::findOrFail($request->role_id);
        $permissions = $role->permissions->map(function($permission) {
            $parts = explode('_', $permission->name);
            return [
                'id' => $permission->id,
                'group' => $parts[1] ?? '',
                'action' => $parts[0] ?? '',
            ];
        });

        return response()->json(['permissions' => $permissions]);
    }

}
