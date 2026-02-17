<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    protected $fillable = [
        'module',
        'from_status',
        'to_status',
        'title',
        'message',
        'channel',
        'priority',
        'is_important',
        'action_required',
        'is_active',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'action_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'notification_rule_role', 'notification_rule_id', 'role_id')->withTimestamps();
    }
}
