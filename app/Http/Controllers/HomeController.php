<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $household = $user->householdOpenedLast;

        if ($household instanceof Household && $household->hasMember($user)) {
            return to_route('search.index', $household);
        }

        return to_route('households.index');
    }
}
