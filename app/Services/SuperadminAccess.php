<?php
namespace App\Services;

use App\Models\Admin;

/** Uses the existing admin guard and role; override is a confirmed operation, not a new permission. */
class SuperadminAccess
{
    public static function isSuperadmin(): bool
    {
        return auth('admin')->user() instanceof Admin && auth('admin')->user()->role === 'superadmin';
    }

    public static function confirmedAccountChange(): bool
    {
        if (!self::isSuperadmin()) return false;
        request()->validate(['confirmed'=>'accepted','override_reason'=>'required|string|min:8|max:2000']);
        return true;
    }

    public static function overriding(): bool
    {
        return false; // Workflow stages cannot be bypassed by any role.
    }
}
