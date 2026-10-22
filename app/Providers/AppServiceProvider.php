<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\User;
use App\Policies\EventPolicy;
use App\Support\Rbac;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Model::preventLazyLoading(! $this->app->isProduction());

        Gate::policy(Event::class, EventPolicy::class);
        Gate::before(fn (User $user): ?bool => $user->hasRole(Rbac::SUPER_ADMIN) ? true : null);
    }
}
