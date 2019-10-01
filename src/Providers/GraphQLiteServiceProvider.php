<?php

namespace TheCodingMachine\GraphQLite\Laravel\Providers;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use TheCodingMachine\GraphQLite\Laravel\Mappers\PaginatorTypeMapper;
use TheCodingMachine\GraphQLite\Laravel\Mappers\PaginatorTypeMapperFactory;
use TheCodingMachine\GraphQLite\Laravel\Security\AuthenticationService;
use TheCodingMachine\GraphQLite\Laravel\Security\AuthorizationService;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use function config;
use function extension_loaded;
use GraphQL\Error\Debug;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use function ini_get;
use function is_array;
use function is_iterable;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use TheCodingMachine\GraphQLite\Laravel\Controllers\GraphQLiteController;
use TheCodingMachine\GraphQLite\Laravel\Middlewares\GraphQLMiddleware;
use TheCodingMachine\GraphQLite\Laravel\SanePsr11ContainerAdapter;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use GraphQL\Type\Schema as WebonyxSchema;

class GraphQLiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/graphqlite.php' => config_path('graphqlite.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(WebonyxSchema::class, Schema::class);

        $this->app->bind(HttpMessageFactoryInterface::class, DiactorosFactory::class);

        $this->app->singleton(GraphQLiteController::class, function (Application $app) {
            $debug = config('graphqlite.debug', Debug::RETHROW_UNSAFE_EXCEPTIONS);

            return new GraphQLiteController($app[StandardServer::class], $app[HttpMessageFactoryInterface::class], $debug);
        });

        $this->app->singleton(StandardServer::class, function (Application $app) {
            return new StandardServer($app[ServerConfig::class]);
        });

        $this->app->singleton(ServerConfig::class, function (Application $app) {
            $serverConfig = new ServerConfig();
            $serverConfig->setSchema($app[Schema::class]);
            return $serverConfig;
        });

        $this->app->singleton('graphqliteCache', function () {
            if (extension_loaded('apcu') && ini_get('apc.enabled')) {
                return new \Symfony\Component\Cache\Simple\ApcuCache();
            } else {
                return new \Symfony\Component\Cache\Simple\PhpFilesCache();
            }
        });

        $this->app->singleton(AuthenticationService::class, function(Application $app) {
            $guard = config('graphqlite.guard', $this->app['config']['auth.defaults.guard']);
            if (!is_array($guard)) {
                $guard = [$guard];
            }
            return new AuthenticationService($app[AuthFactory::class], $guard);
        });

        $this->app->bind(AuthenticationServiceInterface::class, AuthenticationService::class);

        $this->app->singleton(SchemaFactory::class, function (Application $app) {
            $service = new SchemaFactory($app->make('graphqliteCache'), new SanePsr11ContainerAdapter($app));
            $service->setAuthenticationService($app[AuthenticationService::class]);
            $service->setAuthorizationService($app[AuthorizationService::class]);
            $service->addTypeMapperFactory($app[PaginatorTypeMapperFactory::class]);

            $controllers = config('graphqlite.controllers', 'App\\Http\\Controllers');
            if (!is_iterable($controllers)) {
                $controllers = [ $controllers ];
            }
            $types = config('graphqlite.types', 'App\\');
            if (!is_iterable($types)) {
                $types = [ $types ];
            }
            foreach ($controllers as $namespace) {
                $service->addControllerNamespace($namespace);
            }
            foreach ($types as $namespace) {
                $service->addTypeNamespace($namespace);
            }

            if ($this->app->environment('production')) {
                $service->prodMode();
            }

            return $service;
        });

        $this->app->singleton(Schema::class, function (Application $app) {
            /** @var SchemaFactory $schemaFactory */
            $schemaFactory = $app->make(SchemaFactory::class);

            return $schemaFactory->createSchema();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            SchemaFactory::class,
            Schema::class,
        ];
    }
}
