<?php

namespace Irabbi360\LaravelApiInspector;

use Irabbi360\LaravelApiInspector\Commands\GenerateDocsCommand;
use Irabbi360\LaravelApiInspector\Commands\LaravelApiInspectorCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelApiInspectorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-api-inspector')

            // config/api-inspector.php
            ->hasConfigFile()

            // resources/views
            ->hasViews()

            // database/migrations
            ->hasMigration('create_laravel_api_inspector_table')

            // routes/web.php
            ->hasRoutes(['web', 'api'])

            // artisan commands
            ->hasCommands([
                GenerateDocsCommand::class,
                LaravelApiInspectorCommand::class,
            ])

            // 👇 THIS IS THE IMPORTANT PART
            ->hasAssets();
    }
}
