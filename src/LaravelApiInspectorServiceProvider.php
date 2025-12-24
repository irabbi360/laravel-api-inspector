<?php

namespace Irabbi360\LaravelApiInspector;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Irabbi360\LaravelApiInspector\Commands\PublishCommand;
use Irabbi360\LaravelApiInspector\Commands\GenerateDocsCommand;
use Irabbi360\LaravelApiInspector\Commands\LaravelApiInspectorCommand;

class LaravelApiInspectorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-api-inspector')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoutes(['web', 'api'])
            ->hasAssets()
            // artisan commands
            ->hasCommands([
                GenerateDocsCommand::class,
                LaravelApiInspectorCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->startWith(function (InstallCommand $command): void {
                        $command->info('Installing Laravel API Inspector...');
                        $command->info('This package will help you generate auto API documentation.');
                    })
                    ->publishConfigFile()
                    ->publishAssets()
                    ->endWith(function (InstallCommand $command): void {
                        $command->info('Laravel API Inspector has been installed successfully!');
                        $command->info('You can now visit /api-docs to view your API documentation.');
                        $command->info('Check the documentation for configuration options.');
                    })
                    ->askToStarRepoOnGitHub('irabbi360/laravel-api-inspector');
            });
    }
}
