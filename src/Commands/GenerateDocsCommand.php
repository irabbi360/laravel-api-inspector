<?php

namespace Irabbi360\LaravelApiInspector\Commands;

use Illuminate\Console\Command;

class GenerateDocsCommand extends Command
{
    public $signature = 'api-inspector:generate';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
