<?php

namespace App\Policies;

use App\Models\SolarCase;
use App\Models\User;

class SolarCasePolicy
{
    public function view(User $user, SolarCase $case): bool
    {
        return $user->is_active && ! $case->client->trashed()
            && $user->can('receipts.view') && $user->can('view', $case->client);
    }

    public function process(User $user, SolarCase $case): bool
    {
        return $this->view($user, $case) && $user->can('receipts.import');
    }
}
