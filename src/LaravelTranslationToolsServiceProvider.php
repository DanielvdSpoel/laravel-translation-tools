<?php

namespace danielvdspoel\LaravelTranslationTools;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use danielvdspoel\LaravelTranslationTools\Commands\CheckTranslationsCommand;

class LaravelTranslationToolsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-translation-tools')
            ->hasCommand(CheckTranslationsCommand::class);
    }
}
