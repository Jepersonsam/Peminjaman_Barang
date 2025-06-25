<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\AuthCode;
use Laravel\Passport\Client;
use Laravel\Passport\DeviceCode;
use Laravel\Passport\Passport;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Passport::useTokenModel(Token::class);
        Passport::useRefreshTokenModel(RefreshToken::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::useClientModel(Client::class);
        Passport::useDeviceCodeModel(DeviceCode::class);

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });
    }
}
