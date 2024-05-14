<?php

namespace danielvdspoel\LaravelTranslationTools;

use danielvdspoel\LaravelTranslationTools\Commands\LaravelTranslationToolsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-translation-tools_table')
            ->hasCommand(LaravelTranslationToolsCommand::class);
    }
}
