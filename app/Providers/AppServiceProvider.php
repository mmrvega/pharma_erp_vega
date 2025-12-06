<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load custom helper functions (translations, etc.)
        $helpers = app_path('Helpers/TranslationHelper.php');
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }
}
