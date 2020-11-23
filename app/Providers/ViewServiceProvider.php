<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...
        View::composer(
            'location-menu', 'App\Http\View\Composers\LocationMenuComposer'
        );
        View::composer(
            'layouts.main', 'App\Http\View\Composers\SiteComposer'
        );
    }
}
