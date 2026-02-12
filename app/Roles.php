<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class Roles extends Model
{
    use HasRoles;

    protected $fillable = [
        'name',
        'guard_name'
    ];

    public function permissions(){
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    /**
     * Assign all actions for a specific permission.
     *
     * @param string $permissionName
     * @return void
     */
    public function givePermissionToActions($permissionName){
        foreach (['view', 'add', 'edit', 'delete','print'] as $action) {
            $this->givePermissionTo("{$action}_{$permissionName}");
        }
    }
}
