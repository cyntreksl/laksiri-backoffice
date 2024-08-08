<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timezone = $this->getTimezoneFromCountry(Session::get('current_branch_name'));
            if ($timezone) {
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        }

        return $next($request);
    }

    /**
     * Get the timezone from the country.
     */
    protected function getTimezoneFromCountry(string $branch): ?string
    {
        // You need a map of countries to timezones.
        // This is a simplified example. You'll need a more comprehensive mapping.
        $timezones = [
            'Riyadh' => 'Asia/Riyadh',
            'Colombo' => 'Asia/Colombo',
            'Dubai' => 'Asia/Dubai',
            'Kuwait' => 'Asia/Kuwait',
            'Quatar' => 'Asia/Qatar',
            'Qatar' => 'Asia/Qatar',
            'Malaysia' => 'Asia/Kolkata',
            'Nintavur' => 'Asia/Colombo',
            'London' => 'Europe/London',
            'Sri Lanka' => 'Asia/Colombo',
        ];

        return $timezones[$branch] ?? null;
    }
}
