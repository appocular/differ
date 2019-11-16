<?php

declare(strict_types=1);

namespace Appocular\Differ\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app['auth']->viaRequest('shared_token', static function ($request): ?Authenticatable {
            if ($request->bearerToken() && $request->bearerToken() === \env('SHARED_TOKEN')) {
                // phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
                // phpcs:disable SlevomatCodingStandard.ControlStructures.ControlStructureSpacing.IncorrectLinesCountAfterControlStructure
                // phpcs:disable SlevomatCodingStandard.ControlStructures.ControlStructureSpacing.IncorrectLinesCountBeforeFirstControlStructure
                // phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
                // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                return new class implements Authenticatable {
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
                // phpcs:enable
            }

            return null;
        });
    }
}
