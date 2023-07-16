<?php

namespace NovaChunkedVideo;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nova-chunked.php' => config_path('nova-chunked.php'),
            ], 'config');


            $this->commands([
            ]);
        }

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('chunked-video', __DIR__.'/../dist/js/field.js');
            Nova::style('chunked-video', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-chunked.php', 'nova-chunked');
    }

    /**
     * Register the card's routes.
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
             ->prefix('nova-vendor/nova-chunked')
             ->group(function () {
                 if (config('nova-chunked.use_package_routes')) {
                     Route::post(
                         '/video-upload/{resource}/{resourceId}/{field}',
                         [ \NovaChunkedVideo\Http\Controllers\VideoController::class, 'store' ]
                     )->name('nova.chunked-video.upload');
                 }
             });
    }
}
