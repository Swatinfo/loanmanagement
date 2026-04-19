<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPermissionGates();
        $this->registerImpersonationListeners();

        // Opt-in N+1 detection. Set STRICT_LAZY_LOADING=true in .env when
        // hunting eager-load bugs. Off by default — middleware, navigation,
        // and role checks touch $user->roles everywhere, so making it always-on
        // in local creates more noise than signal.
        Model::preventLazyLoading((bool) env('STRICT_LAZY_LOADING', false));
    }

    /**
     * Super-admin bypass + resolve any ability matching a permission slug via PermissionService.
     * Preserves 3-tier order: super_admin → user override (grant/deny) → role default.
     */
    protected function registerPermissionGates(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            $service = app(PermissionService::class);
            if (in_array($ability, $service->allSlugs(), true)) {
                return $service->userHasPermission($user, $ability);
            }

            return null;
        });
    }

    protected function registerImpersonationListeners(): void
    {
        Event::listen(TakeImpersonation::class, function (TakeImpersonation $event): void {
            ActivityLog::log('impersonate_start', $event->impersonated, [
                'original_user_id' => $event->impersonator->id,
                'impersonator_name' => $event->impersonator->name,
                'impersonated_name' => $event->impersonated->name,
            ]);
        });

        Event::listen(LeaveImpersonation::class, function (LeaveImpersonation $event): void {
            ActivityLog::log('impersonate_end', $event->impersonated, [
                'original_user_id' => $event->impersonator->id,
                'impersonator_name' => $event->impersonator->name,
                'impersonated_name' => $event->impersonated->name,
            ]);
        });
    }
}
