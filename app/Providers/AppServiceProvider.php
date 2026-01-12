<?php

namespace App\Providers;

use App\Events\PickupCollected;
use App\Events\PickupCreated;
use App\Events\PickupDriverAssigned;
use App\Events\ShipmentDepartured;
use App\Events\UpdateLastLogin;
use App\Events\UpdateLastLogout;
use App\Listeners\SendPickupCollectedNotification;
use App\Listeners\SendPickupCreatedNotification;
use App\Listeners\SendPickupDriverAssignedNotification;
use App\Listeners\SendShipmentDeparturedNotification;
use App\Listeners\SetUserCurrentBranch;
use App\Models\Token;
use App\Observers\TokenObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
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
        $this->app->register(PaymentCalculationServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (App::environment() == 'production') {
            URL::forceScheme('https');
        }

        $emailTest = config('mail.test.address', true);
        if ($emailTest) {
            Mail::alwaysTo($emailTest);
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

        Event::listen(PickupCreated::class, SendPickupCreatedNotification::class);
        Event::listen(PickupDriverAssigned::class, SendPickupDriverAssignedNotification::class);
        Event::listen(PickupCollected::class, SendPickupCollectedNotification::class);
        Event::listen(ShipmentDepartured::class, SendShipmentDeparturedNotification::class);

        // Register Token observer for automatic status updates
        Token::observe(TokenObserver::class);
    }
}
