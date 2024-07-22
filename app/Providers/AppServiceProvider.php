<?php

namespace App\Providers;

use App\Events\UpdateLastLogin;
use App\Events\UpdateLastLogout;
use App\Listeners\SetUserCurrentBranch;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (App::environment() == 'production') {
            URL::forceScheme('https');
        }

        Passport::enablePasswordGrant();
        Event::listen(
            UpdateLastLogin::class,
            UpdateLastLogout::class,
        );

        Event::listen(Login::class, SetUserCurrentBranch::class);

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
