<?php

namespace TheCodingMachine\GraphQLite\Laravel\Providers;

use GraphQL\Error\Debug;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
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

        $this->app->singleton(SchemaFactory::class, function (Application $app) {
            $service = new SchemaFactory($app->make(Repository::class), new SanePsr11ContainerAdapter($app));

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
