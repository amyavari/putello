<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Household;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SaveCurrentHousehold
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Household $household */
        $household = $request->route('household');

        if (! $this->isAlreadySaved($user, $household)) {
            $user->update(['current_household_id' => $household->id]);
        }

        return $next($request);
    }

    private function isAlreadySaved(User $user, Household $household): bool
    {
        return $user->current_household_id === $household->id;
    }
}
