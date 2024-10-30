<?php

namespace Wisdech\SMSVerify;

use Illuminate\Support\ServiceProvider;

class SMSVerifyProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SMSVerify::class, function ($app) {
            return new SMSVerify();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/verify.php' => config_path('verify.php'),
        ], 'sms-verify-config');
    }
}
