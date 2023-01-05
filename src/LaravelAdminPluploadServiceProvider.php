<?php

namespace afunjoker\LaravelAdminPlupload;

use Illuminate\Support\ServiceProvider;

class LaravelAdminPluploadServiceProvider extends ServiceProvider
{
    /**
     * {}
     */
    public function boot(LaravelAdminPlupload $extension)
    {
        if (!LaravelAdminPlupload::boot()) {
            return;
        }

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'laravel-admin-plupload');
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/laravel_admin_plupload.php' => config_path('laravel_admin_plupload.php'),
                    $assets => public_path('vendor/afunjoker/laravel-admin-plupload')
                ],
                'laravel-admin-plupload'
            );
        }

        $this->app->booted(function () {
            LaravelAdminPlupload::routes(__DIR__ . '/../routes/web.php');
        });
    }
}