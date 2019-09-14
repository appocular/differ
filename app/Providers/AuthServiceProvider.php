<?php

namespace Appocular\Differ\Providers;

use Appocular\Differ\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('shared_token', function ($request) {
            if ($request->bearerToken() && $request->bearerToken() == env('SHARED_TOKEN')) {
                return new class implements \Illuminate\Contracts\Auth\Authenticatable {
                    public function getAuthIdentifierName()
                    {
                        return 'name';
                    }
                    public function getAuthIdentifier()
                    {
                        return 'shared';
                    }
                    public function getAuthPassword()
                    {
                    }
                    public function getRememberToken()
                    {
                    }
                    public function setRememberToken($value)
                    {
                    }
                    public function getRememberTokenName()
                    {
                    }
                };
            }
        });
    }
}
