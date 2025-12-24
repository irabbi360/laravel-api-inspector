<?php

namespace Irabbi360\LaravelApiInspector\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Irabbi360\LaravelApiInspector\Facades\LaravelApiInspector;
use Spatie\Watcher\Watch;

class PublishCommand extends Command
{
    public $signature = 'laravel-api-inspector:publish  {--watch}';

    public $description = 'Publish the Laravel API Inspector assets';

    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'api-inspector-assets',
            '--force' => true,
        ]);

        if ($this->option('watch')) {
            if (! class_exists(Watch::class)) {
                $this->error('Please install the spatie/file-system-watcher package to use the --watch option.');
                $this->info('Learn more at https://github.com/spatie/file-system-watcher');

                return;
            }

            $this->info('Watching for file changes... (Press CTRL+C to stop)');

            Watch::path(LaravelApiInspector::basePath('/public'))
                ->onAnyChange(function (string $type, string $path) {
                    if (Str::endsWith($path, 'manifest.json')) {
                        $this->call('vendor:publish', [
                            '--tag' => 'api-inspector-assets',
                            '--force' => true,
                        ]);
                    }
                })
                ->start();
        }
    }
}
