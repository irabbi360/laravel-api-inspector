<?php

namespace Irabbi360\LaravelApiInspector;

use Composer\InstalledVersions;

class LaravelApiInspector
{
    /**
     * Get the current version of the Log Viewer
     */
    public function version(): string
    {
        if (app()->runningUnitTests()) {
            return 'unit-tests';
        }

        if (class_exists(InstalledVersions::class)) {
            return InstalledVersions::getPrettyVersion('irabbi360/laravel-api-inspector') ?? 'dev-main';
        } else {
            $composerJson = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);

            return is_array($composerJson) && isset($composerJson['version'])
                ? $composerJson['version']
                : 'dev-main';
        }
    }
}
