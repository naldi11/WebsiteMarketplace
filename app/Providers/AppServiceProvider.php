<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share dispute badge count ke semua admin views
        View::composer('layouts.admin', function ($view) {
            if (auth()->check() && auth()->user()->role === 'admin') {
                $view->with('disputeNavBadge',
                    \App\Models\Dispute::whereIn('status', ['open', 'admin_reviewing'])->count()
                );
            } else {
                $view->with('disputeNavBadge', 0);
            }
        });
    }
}
