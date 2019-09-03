<?php

namespace Tumainimosha\MpesaPush;

use Illuminate\Support\ServiceProvider;

class MpesaPushServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/mpesa-push.php';

    const MIGRATION_PATH = __DIR__ . '/../database/migrations';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('mpesa-push.php'),
        ], 'config');

        $this->publishes([
            self::MIGRATION_PATH => database_path('migrations'),
        ], 'config');

        /*
         * Optional methods to load your package assets
         */
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'LaravelMpesaPush');
        $this->loadMigrationsFrom(self::MIGRATION_PATH);
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'mpesa-push'
        );

        $this->app->singleton(WsClient::class, function ($app) {
            return WsClient::instance();
        });
    }
}
