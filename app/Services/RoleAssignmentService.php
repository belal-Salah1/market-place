<?php

namespace App\Services;

use App\Enums\RoleStatus;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleAssignmentService
{
    /**
     * Determine the role status for a new user during registration.
     */
    public function determineRoleForRegistration(Request $request): RoleStatus
    {
        $adminEmails = config('auth.admin_emails', []);

        if (in_array($request->email, $adminEmails)) {
            return RoleStatus::ADMIN;
        }

        if ($request->boolean('isVendor')) {
            return RoleStatus::VENDOR;
        }

        return RoleStatus::CUSTOMER;
    }

    /**
     * Get the role model for the given role status.
     */
    public function getRoleByStatus(RoleStatus $roleStatus): Role
    {
        return Role::where('name', $roleStatus->value)->firstOrFail();
    }
}
