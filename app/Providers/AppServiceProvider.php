<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Observers\AuditObserver;

// models to observe will be registered dynamically if they exist
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\Container;
use App\Models\Warehouse;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;

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
        $this->configureDefaults();
        $this->registerAuditObservers();
    }

    protected function registerAuditObservers(): void
    {
        $models = [
            Customer::class,
            Shipment::class,
            Container::class,
            Warehouse::class,
            Payment::class,
            Receipt::class,
            User::class,
        ];

        foreach ($models as $m) {
            if (class_exists($m)) {
                try {
                    $m::observe(AuditObserver::class);
                } catch (\Throwable $e) {
                    // ignore failures registering observers
                }
            }
        }
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
