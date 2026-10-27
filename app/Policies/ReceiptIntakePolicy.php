<?php

namespace App\Policies;

use App\Models\ReceiptIntake;
use App\Models\User;

class ReceiptIntakePolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->can('receipts.import');
    }

    public function view(User $user, ReceiptIntake $intake): bool
    {
        return $user->is_active && ! $intake->client->trashed()
            && $user->can('receipts.view')
            && $user->can('view', $intake->client);
    }

    public function process(User $user, ReceiptIntake $intake): bool
    {
        return $this->view($user, $intake) && $user->can('receipts.import');
    }
}
