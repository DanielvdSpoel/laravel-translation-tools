<?php

namespace danielvdspoel\LaravelTranslationTools\Commands;

use Illuminate\Console\Command;

class CheckTranslationsCommand extends Command
{
    public $signature = 'translations:check ';

    public $description = 'Check translations for missing keys or values.';

    public function handle(): int
    {
        $this->comment('This is a test command.');

        trans('test', [], 'nl');

        return self::SUCCESS;
    }
}
