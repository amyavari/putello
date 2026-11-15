<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Override;

final class AppServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configureUrl();
        $this->configureDates();
        $this->configureFormRequests();
        $this->configurePasswords();
    }

    /**
     * Blocks db:wipe, migrate:fresh, migrate:refresh, migrate:reset and migrate:rollback in production.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            (bool) $this->app->environment('production')
        );
    }

    /**
     * shouldBeStrict() stops lazy loading, silently discarded attributes and access to missing attributes.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict();
        Model::unguard();
        Model::automaticallyEagerLoadRelationships();
    }

    private function configureUrl(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Every change to a date returns a new instance.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureFormRequests(): void
    {
        FormRequest::failOnUnknownFields();
    }

    private function configurePasswords(): void
    {
        Password::defaults(fn (): ?Password => $this->app->environment('production')
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
