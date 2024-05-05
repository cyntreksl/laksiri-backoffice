<?php

namespace App\Listeners;

use App\Actions\Branch\GetBranchById;
use App\Actions\Branch\GetBranchByName;
use App\Actions\User\SwitchUserBranch;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        $branch = GetBranchById::run($user->primary_branch_id);
        SwitchUserBranch::run($branch);
    }
}
