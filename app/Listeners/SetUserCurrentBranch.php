<?php

namespace App\Listeners;

use App\Actions\Branch\GetBranchById;
use App\Actions\User\SwitchUserBranch;
use Illuminate\Auth\Events\Login;

class SetUserCurrentBranch
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $branch = GetBranchById::run($user->last_logged_branch_id ?? $user->primary_branch_id);
        SwitchUserBranch::run($branch);
    }
}
