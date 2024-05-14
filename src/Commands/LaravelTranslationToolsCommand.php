<?php

namespace danielvdspoel\LaravelTranslationTools\Commands;

use Illuminate\Console\Command;

class LaravelTranslationToolsCommand extends Command
{
    public $signature = 'laravel-translation-tools';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
