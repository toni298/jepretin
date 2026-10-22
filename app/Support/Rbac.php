<?php

namespace App\Support;

final class Rbac
{
    public const ADMIN = 'admin';

    public const SUPER_ADMIN = 'super admin';

    public const EVENT_PERMISSIONS = [
        'events.view',
        'events.create',
        'events.edit',
        'events.delete',
    ];
}
