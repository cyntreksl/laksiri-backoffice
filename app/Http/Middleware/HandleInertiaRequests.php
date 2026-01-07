<?php

namespace App\Http\Middleware;

use App\Actions\Branch\GetBranchById;
use App\Actions\CurrencyRate\GetLatestCurrencyRateByBranchCurrencyCode;
use App\Actions\User\GetUserCurrentBranchID;
use App\Enum\BranchType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $currentBranch = Auth::check() ? GetBranchById::run(GetUserCurrentBranchID::run()) : null;

        $currencyRate = null;

        if ($currentBranch && $currentBranch->type === BranchType::DEPARTURE->value) {
            $currencyRate = GetLatestCurrencyRateByBranchCurrencyCode::run($currentBranch->currency_symbol);
        }

        return [
            ...parent::share($request),
            'userBranch' => Auth::check() ? Auth::user()->branches()->pluck('name')->toArray() : [],
            'currentBranch' => $currentBranch,
            'currentBranchCurrencyRate' => $currencyRate,
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'user.roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
            'user.permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
            'csrf' => csrf_token(),
            'pusher' => [
                'key' => env('PUSHER_APP_KEY'),
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'forceTLS' => true,
            ],
        ];
    }
}
