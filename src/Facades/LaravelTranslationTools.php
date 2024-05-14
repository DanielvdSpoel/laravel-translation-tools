<?php

namespace danielvdspoel\LaravelTranslationTools\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \danielvdspoel\LaravelTranslationTools\LaravelTranslationTools
 */
class LaravelTranslationTools extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \danielvdspoel\LaravelTranslationTools\LaravelTranslationTools::class;
    }
}
