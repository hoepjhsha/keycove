<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\KycStatus;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerPortalApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $user->loadMissing('seller');

            if ($user->seller === null || $user->role !== UserRole::Seller || $user->seller->kyc_status !== KycStatus::Approved) {
                return redirect()
                    ->route('seller.apply')
                    ->with('seller-status', 'Complete and submit your seller application first.');
            }
        }

        return $next($request);
    }
}
