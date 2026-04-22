<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk;

use GiorgiGrdzelidze\BogSdk\Auth\TokenManager;
use GiorgiGrdzelidze\BogSdk\Console\PublishAppleDomainAssociationCommand;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel service provider for the BOG SDK.
 *
 * Registers singletons for TokenManager, HttpClient, and BogClient,
 * and publishes config and key files.
 */
final class BogSdkServiceProvider extends ServiceProvider
{
    /**
     * Register SDK services into the container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bog-sdk.php', 'bog-sdk');

        $this->app->singleton(TokenManager::class, function ($app): TokenManager {
            return new TokenManager(
                cache: $app->make(CacheRepository::class),
                http: $app->make(Factory::class),
                config: (array) config('bog-sdk'),
            );
        });

        $this->app->singleton(HttpClient::class, function ($app): HttpClient {
            return new HttpClient(
                http: $app->make(Factory::class),
                tokens: $app->make(TokenManager::class),
                config: (array) config('bog-sdk'),
            );
        });

        $this->app->singleton(BogClient::class, function ($app): BogClient {
            return new BogClient(
                http: $app->make(HttpClient::class),
                httpFactory: $app->make(Factory::class),
                config: (array) config('bog-sdk'),
            );
        });

        $this->app->alias(BogClient::class, 'bog');
    }

    /**
     * Bootstrap SDK services (publish config, keys, and console commands).
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bog-sdk.php' => config_path('bog-sdk.php'),
            ], 'bog-sdk-config');

            $this->publishes([
                __DIR__.'/../resources/keys/bog-payments-callback.pem' => storage_path('app/bog-sdk/bog-payments-callback.pem'),
            ], 'bog-sdk-keys');

            $this->commands([
                PublishAppleDomainAssociationCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [BogClient::class, 'bog', TokenManager::class, HttpClient::class];
    }
}
