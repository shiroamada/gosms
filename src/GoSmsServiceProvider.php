<?php

namespace NotificationChannels\GoSms;

use Illuminate\Support\ServiceProvider;

class GoSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(GoSmsApi::class, function () {
            $config = config('services.gosms');

            return new GoSmsApi($config['login'], $config['secret'], $config['sender']);
        });
    }
}
