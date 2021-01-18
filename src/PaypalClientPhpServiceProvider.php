<?php

namespace Samyakrt\PaypalClientPhp;

use Illuminate\Support\ServiceProvider;

class PaypalClientPhpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'paypal-client-php');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'paypal-client-php');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('paypal-client-php.php'),
            ], 'config');

            $this->mergeConfigFrom(__DIR__.'/../config/config.php','paypal');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/paypal-client-php'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/paypal-client-php'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/paypal-client-php'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'paypal-client-php');

        // Register the main class to use with the facade
        $this->app->singleton('paypal-client-php', function () {
            return new PaypalClientPhp;
        });
    }
}
