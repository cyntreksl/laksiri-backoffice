<?php

namespace App\Http\Middleware;

use App\Actions\User\GetUserCurrentBranch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetBranchTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $branch = GetUserCurrentBranch::run();

            if ($branch && $branch['timezone']) {
                Config::set('app.timezone', $branch['timezone']);
                date_default_timezone_set($branch['timezone']);
            }
        }

        return $next($request);
    }
}
