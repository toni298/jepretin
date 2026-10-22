<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('events.view');
    }

    public function view(User $user, Event $event): bool
    {
        return (int) $user->id === (int) $event->user_id && $user->checkPermissionTo('events.view');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('events.create');
    }

    public function update(User $user, Event $event): bool
    {
        return (int) $user->id === (int) $event->user_id && $user->checkPermissionTo('events.edit');
    }

    public function delete(User $user, Event $event): bool
    {
        return (int) $user->id === (int) $event->user_id && $user->checkPermissionTo('events.delete');
    }

    public function uploadCover(User $user): bool
    {
        return $user->checkPermissionTo('events.create')
            || $user->checkPermissionTo('events.edit');
    }
}
