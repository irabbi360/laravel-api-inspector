<?php

namespace Irabbi360\LaravelApiInspector;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Irabbi360\LaravelApiInspector\Commands\LaravelApiInspectorCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_api_inspector_table')
            ->hasCommand(LaravelApiInspectorCommand::class);
    }
}
