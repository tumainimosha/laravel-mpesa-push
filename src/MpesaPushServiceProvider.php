<?php

namespace Tumainimosha\MpesaPush;

use Illuminate\Support\ServiceProvider;

class MpesaPushServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/mpesa-push.php';

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
        /*
         * Optional methods to load your package assets
         */
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'LaravelMpesaPush');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
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

        // WSDL
        $wsdl = __DIR__ . '/../files/ussd_push.wsdl';

        // URL
        $url = config('mpesa-push.endpoint');

        // Certificates
        $caFile = config('mpesa-push.ca_file');
        $certFile = config('mpesa-push.ssl_cert');
        $sslKeyFile = config('mpesa-push.ssl_key');
        $sslKeyPasswd = config('mpesa-push.ssl_cert_password');

        $context = stream_context_create([
            'ssl' => [
                'cafile' => $caFile,
                'local_cert' => $certFile,
                'local_pk' => $sslKeyFile,
                'passphrase' => $sslKeyPasswd,
                //'ciphers'=>'AES256-SHA'
            ], ]);

        $client = new \Tumainimosha\MpesaPush\WsClient($wsdl, [
            'stream_context' => $context,
            'location' => $url,
            // other options
            'exceptions' => true,
            'trace' => 1,
            'connection_timeout' => 10,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        $this->app->singleton(WsClient::class, function ($app) use ($client) {
            return $client;
        });
    }
}
