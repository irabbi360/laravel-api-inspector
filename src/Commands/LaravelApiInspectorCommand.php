<?php

namespace Irabbi360\LaravelApiInspector\Commands;

use Illuminate\Console\Command;

class LaravelApiInspectorCommand extends Command
{
    public $signature = 'laravel-api-inspector';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
