<?php

namespace App\Domain\Identity\Services;

use App\Models\User;

/**
 * Enforces the account safety rules that protect the operator of a Helios
 * installation from locking themselves out or leaving the application
 * without any active `owner`.
 */
class UserAccountGuard
{
    /**
     * A user may never deactivate their own account.
     */
    public function canDeactivateSelf(): bool
    {
        return false;
    }

    /**
     * A user may never change their own role.
     */
    public function canChangeOwnRole(): bool
    {
        return false;
    }

    /**
     * Whether $target is currently the last active `owner` in the system.
     */
    public function isLastActiveOwner(User $target): bool
    {
        if (! $target->is_active || ! $target->hasRole('owner')) {
            return false;
        }

        return User::role('owner')->where('is_active', true)->count() <= 1;
    }

    /**
     * Whether $target can be deactivated without leaving the application
     * without any active owner.
     */
    public function canDeactivate(User $target): bool
    {
        return ! $this->isLastActiveOwner($target);
    }

    /**
     * Whether $target's role can be changed to $newRole without leaving the
     * application without any active owner.
     */
    public function canChangeRole(User $target, string $newRole): bool
    {
        if ($newRole === 'owner') {
            return true;
        }

        return ! $this->isLastActiveOwner($target);
    }
}
