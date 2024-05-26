<?php

namespace App\Providers;

use App\Events\UpdateLastLogin;
use App\Events\UpdateLastLogout;
use App\Listeners\SetUserCurrentBranch;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
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
    }
}
