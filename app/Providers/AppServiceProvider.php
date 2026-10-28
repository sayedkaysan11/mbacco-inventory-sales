<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tampilan pagination memakai Bootstrap 5 (sesuai layout)
        Paginator::useBootstrapFive();

        // Gate dipakai oleh middleware "can:admin" di routes/web.php
        // dan oleh @can('admin') di Blade. Otorisasi sebenarnya ada di SERVER (route + Form Request).
                Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('internal', fn (User $user) => in_array($user->role, ['admin', 'staff'], true));
        Gate::define('customer', fn (User $user) => $user->role === 'customer');
    }
}