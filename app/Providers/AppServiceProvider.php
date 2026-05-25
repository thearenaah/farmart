<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (env('FORCE_SCHEMA') === 'https') {
            URL::forceScheme('https');
        }

        if (env('FORCE_ROOT_URL')) {
            URL::forceRootUrl(env('FORCE_ROOT_URL'));
        }
    }
}
