<?php namespace Dilexus\Octobase;

use Dilexus\Octobase\Classes\Api\Middleware\OctobaseAuthAdmin;
use Dilexus\Octobase\Classes\Api\Middleware\OctobaseAuthPublic;
use Dilexus\Octobase\Classes\Api\Middleware\OctobaseAuthRegistered;
use Dilexus\Octobase\Classes\Api\Middleware\OctobaseAuthRestricted;
use System\Classes\PluginBase;

/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        $this->app->singleton('obRegistered', function ($app) {
            return new OctobaseAuthRegistered;
        });

        $this->app->singleton('obAdmin', function ($app) {
            return new OctobaseAuthAdmin;
        });

        $this->app->singleton('obPublic', function ($app) {
            return new OctobaseAuthPublic;
        });

        $this->app->singleton('obRestricted', function ($app) {
            return new OctobaseAuthRestricted;
        });

    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
