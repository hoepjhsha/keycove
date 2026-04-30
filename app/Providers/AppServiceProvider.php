<?php

namespace App\Providers;

use App\Enums\ComplaintStatus;
use App\Http\Middleware\EnsureSellerEmailVerified;
use App\Models\Complaint;
use App\Utilities\StorageUtility;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        $loader = AliasLoader::getInstance();
        $loader->alias('StorageUtility', StorageUtility::class);

        Livewire::addPersistentMiddleware([
            EnsureSellerEmailVerified::class,
        ]);

        View::composer('components.partials.dashboard.sidebar', function ($view): void {
            $view->with('openComplaintCount', Complaint::where('status', ComplaintStatus::Open->value)->count());
        });
    }
}
