<?php

namespace App\Providers;

use App\Repositories\Contracts\OrderRepository as OrderRepositoryContract;
use App\Repositories\Contracts\ProductRepository as ProductRepositoryContract;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\Order\Contracts\OrderService as OrderServiceContract;
use App\Services\Order\OrderService;
use App\Services\Product\Contracts\ProductService as ProductServiceContract;
use App\Services\Product\ProductService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductServiceContract::class, ProductService::class);
        $this->app->bind(OrderServiceContract::class, OrderService::class);
        $this->app->bind(ProductRepositoryContract::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryContract::class, OrderRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
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
            : null,
        );
    }
}
