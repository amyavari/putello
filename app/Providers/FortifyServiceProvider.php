<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use Override;

final class FortifyServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configurePasswordConfirmation();
    }

    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    private function configureViews(): void
    {
        Fortify::loginView(fn (): Factory|View => view('livewire.auth.login'));
        Fortify::verifyEmailView(fn (): Factory|View => view('livewire.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn (): Factory|View => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn (): Factory|View => view('livewire.auth.confirm-password'));
        Fortify::registerView(fn (): Factory|View => view('livewire.auth.register'));
        Fortify::resetPasswordView(fn (): Factory|View => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn (): Factory|View => view('livewire.auth.forgot-password'));
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request): Limit {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }

    /**
     * Only Livewire requests from pages that require a recent password confirmation.
     */
    private function configurePasswordConfirmation(): void
    {
        Livewire::addPersistentMiddleware([
            RequirePassword::class,
        ]);
    }
}
