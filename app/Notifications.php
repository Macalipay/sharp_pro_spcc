<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class Notifications extends Model
{
    protected $fillable = [
        'target_user_id',
        'target_roles',
        'type',
        'reference_id',
        'title',
        'message',
        'icon',
        'url',
        'route_name',
        'route_params',
        'is_read',
        'is_important',
        'is_active',
        'seen_at',
        'read_at',
        'channel',
        'priority',
        'action_required',
        'sender_id',
        'context',
        'tags',
        'expires_at',
        // Legacy fields kept for backward compatibility
        'subject',
        'details',
        'module',
        'source_id',
        'link',
        'status',
        'tagged_id',
        'workstation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'route_params' => 'array',
        'context' => 'array',
        'tags' => 'array',
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'is_active' => 'boolean',
        'action_required' => 'boolean',
        'seen_at' => 'datetime',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function getDisplayTitleAttribute()
    {
        return $this->title ?: $this->subject;
    }

    public function getDisplayMessageAttribute()
    {
        return $this->message ?: $this->details;
    }

    public function getTargetUrlAttribute()
    {
        if (!empty($this->url)) {
            return $this->url;
        }

        if (!empty($this->route_name) && Route::has($this->route_name)) {
            return route($this->route_name, $this->route_params ?: []);
        }

        if (!empty($this->link)) {
            return $this->link;
        }

        return '#';
    }

    public function isForUser(User $user)
    {
        $matchesUser = !empty($this->target_user_id) && (int) $this->target_user_id === (int) $user->id;
        if ($matchesUser) {
            return true;
        }

        $userRoleIds = $user->roles()->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $roleIds = $this->relationLoaded('roles')
            ? $this->roles->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all()
            : $this->roles()->pluck('roles.id')->map(function ($id) {
                return (int) $id;
            })->all();

        $matchesRole = !empty($roleIds) && !empty(array_intersect($roleIds, $userRoleIds));
        if ($matchesRole) {
            return true;
        }

        // Legacy JSON target_roles fallback.
        $targetRoles = $this->target_roles;
        if (is_string($targetRoles)) {
            $targetRoles = json_decode($targetRoles, true);
        }
        $targetRoles = is_array($targetRoles) ? $targetRoles : [];
        $legacyMatches = !empty($targetRoles) && !empty(array_intersect($targetRoles, $userRoleIds));
        if ($legacyMatches) {
            return true;
        }

        $isBroadcast = empty($this->target_user_id) && empty($roleIds) && empty($targetRoles);

        return $isBroadcast;
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'notification_role', 'notification_id', 'role_id')->withTimestamps();
    }
}
