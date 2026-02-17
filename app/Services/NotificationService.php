<?php

namespace App\Services;

use App\Notifications;
use App\User;

class NotificationService
{
    /**
     * Build base notification query for user.
     *
     * @param \App\User $user
     * @param string $channel
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function forUserQuery(User $user, $channel = 'header')
    {
        $roleIds = $user->roles()->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        return Notifications::query()
            ->with('sender', 'roles')
            ->where(function ($query) use ($user, $roleIds) {
                $query->where('target_user_id', $user->id)
                    ->orWhere(function ($subQuery) use ($roleIds) {
                        $subQuery->whereHas('roles', function ($roleQuery) use ($roleIds) {
                            $roleQuery->whereIn('roles.id', $roleIds);
                        });
                    })
                    ->orWhere(function ($broadcastQuery) {
                        $broadcastQuery->whereNull('target_user_id')
                            ->whereDoesntHave('roles');
                    });
            })
            ->where(function ($query) use ($channel) {
                $query->whereNull('channel')
                    ->orWhere('channel', $channel);
            })
            ->where(function ($query) {
                $query->whereNull('is_active')
                    ->orWhere('is_active', true);
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByRaw('COALESCE(is_read, 0) ASC')
            ->orderByRaw('COALESCE(priority, 1) DESC')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Create notification and attach roles by role IDs.
     *
     * @param array $notificationData
     * @param array $roleIds
     * @return \App\Notifications
     */
    public function create(array $notificationData, array $roleIds = [])
    {
        $notification = Notifications::create($notificationData);

        $roleIds = collect($roleIds)
            ->filter(function ($id) {
                return !empty($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        if (!empty($roleIds)) {
            $notification->roles()->sync($roleIds);
        }

        return $notification;
    }

    /**
     * Fetch notifications for a user based on target_user_id or role IDs in pivot.
     *
     * @param \App\User $user
     * @param string $channel
     * @return \Illuminate\Support\Collection
     */
    public function forUser(User $user, $channel = 'header')
    {
        return $this->forUserQuery($user, $channel)->get();
    }

    /**
     * Fetch paginated notifications for a user.
     *
     * @param \App\User $user
     * @param string $channel
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function forUserPaginated(User $user, $channel = 'header', $perPage = 15)
    {
        return $this->forUserQuery($user, $channel)->paginate((int) $perPage);
    }

    /**
     * Convenience helper: notify all users that have at least one of the given role IDs.
     * Uses role pivot to avoid per-user duplication.
     *
     * @param array $notificationData
     * @param array $roleIds
     * @return \App\Notifications
     */
    public function notifyRoles(array $notificationData, array $roleIds)
    {
        $notificationData['target_user_id'] = null;

        return $this->create($notificationData, $roleIds);
    }

    /**
     * Convenience helper: notify a single user directly.
     *
     * @param array $notificationData
     * @param int $targetUserId
     * @return \App\Notifications
     */
    public function notifyUser(array $notificationData, $targetUserId)
    {
        $notificationData['target_user_id'] = (int) $targetUserId;

        return $this->create($notificationData, []);
    }
}
