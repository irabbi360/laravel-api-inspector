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
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-api-inspector')
            ->hasConfigFile('api-inspector')
            ->hasViews()
            ->hasMigration('create_laravel_api_inspector_table')
            ->hasRoute('web')
            ->hasCommands([
                GenerateDocsCommand::class,
                LaravelApiInspectorCommand::class,
            ]);
    }
}
