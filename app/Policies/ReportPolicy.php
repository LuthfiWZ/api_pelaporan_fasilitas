<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function update(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->isAdmin() || $report->user_id === $user->id;
    }
}
