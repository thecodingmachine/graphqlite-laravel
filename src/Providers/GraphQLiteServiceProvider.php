<?php

namespace TheCodingMachine\GraphQLite\Laravel\Providers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use function is_iterable;
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
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(WebonyxSchema::class, Schema::class);

        $this->app->singleton(SchemaFactory::class, function (Application $app) {
            $service = new SchemaFactory($app->make(Repository::class), $app);

            $controllers = config('controllers', 'App\\Http\\Controllers');
            if (!is_iterable($controllers)) {
                $controllers = [ $controllers ];
            }
            $types = config('types', 'App\\');
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
