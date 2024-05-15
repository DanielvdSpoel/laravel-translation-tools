<?php

namespace danielvdspoel\LaravelTranslationTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SplFileInfo;

class CheckTranslationsCommand extends Command
{
    public $signature = 'translations:check {--base-locale= : The base language to compare against.} {--locales=* : The languages to check.} {--source= : The source directory to check.}';

    public $description = 'Check translations for missing keys or values.';

    public function handle(): int
    {
        $baseLanguage = $this->option('base-locale') ?? config('app.locale', config('app.fallback_locale', 'en'));
        $localeRootDirectory = $this->option('source') ?? lang_path();

        $filesystem = app(Filesystem::class);

        if (! $filesystem->exists($localeRootDirectory)) {
            $this->error("The directory `{$localeRootDirectory}` does not exist.");
            return self::FAILURE;
        }

        collect($filesystem->directories($localeRootDirectory))
            ->mapWithKeys(static fn (string $directory): array => [$directory => (string) str($directory)->afterLast(DIRECTORY_SEPARATOR)])
            ->reject(fn (string $locale): bool => $locale === $baseLanguage)
            ->when(
                $locales = $this->option('locales'),
                fn (Collection $availableLocales): Collection => $availableLocales->filter(fn (string $locale): bool => in_array($locale, $locales))
            )
            ->each(function (string $locale, string $localeDir) use ($baseLanguage, $filesystem, $localeRootDirectory) {
                $files = $filesystem->allFiles($localeDir);
                $baseFiles = $filesystem->allFiles(implode(DIRECTORY_SEPARATOR, [$localeRootDirectory, $baseLanguage]));

                $localeFiles = collect($files)->map(fn ($file) => (string) str($file->getPathname())->after(DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR));
                $baseFiles = collect($baseFiles)->map(fn ($file) => (string) str($file->getPathname())->after(DIRECTORY_SEPARATOR . $baseLanguage . DIRECTORY_SEPARATOR));
                $missingFiles = $baseFiles->diff($localeFiles);
                $removedFiles = $localeFiles->diff($baseFiles);
                $path = implode(DIRECTORY_SEPARATOR, [$localeRootDirectory, $locale]);

                if ($missingFiles->count() > 0 && $removedFiles->count() > 0) {
                    $this->warn("[!] Found {$missingFiles->count()} missing translation " . Str::plural('file', $missingFiles->count()) . " and {$removedFiles->count()} removed translation " . Str::plural('file', $missingFiles->count()) . ' for ' . locale_get_display_name($locale, 'en') . ".\n");

                    $this->newLine();
                } elseif ($missingFiles->count() > 0) {
                    $this->warn("[!] Found {$missingFiles->count()} missing translation " . Str::plural('file', $missingFiles->count()) . ' for ' . locale_get_display_name($locale, 'en') . ".\n");

                    $this->newLine();
                } elseif ($removedFiles->count() > 0) {
                    $this->warn("[!] Found {$removedFiles->count()} removed translation " . Str::plural('file', $removedFiles->count()) . ' for ' . locale_get_display_name($locale, 'en') . ".\n");

                    $this->newLine();
                }

                if ($missingFiles->count() > 0 || $removedFiles->count() > 0) {
                    $this->table(
                        [$path, ''],
                        array_merge(
                            array_map(fn (string $file): array => [$file, 'Missing'], $missingFiles->toArray()),
                            array_map(fn (string $file): array => [$file, 'Removed'], $removedFiles->toArray()),
                        ),
                    );

                    $this->newLine();
                }

                collect($files)
                    ->reject(function ($file) use ($baseLanguage, $localeRootDirectory) {
                        return ! file_exists(implode(DIRECTORY_SEPARATOR, [$localeRootDirectory, $baseLanguage, $file->getRelativePathname()]));
                    })
                    ->mapWithKeys(function (SplFileInfo $file) use ($baseLanguage, $localeDir, $localeRootDirectory) {
                        $expectedKeys = require implode(DIRECTORY_SEPARATOR, [$localeRootDirectory, $baseLanguage, $file->getRelativePathname()]);
                        $actualKeys = require $file->getPathname();

                        return [
                            (string) str($file->getPathname())->after("{$localeDir}/") => [
                                'missing' => array_keys(array_diff_key(
                                    Arr::dot($expectedKeys),
                                    Arr::dot($actualKeys)
                                )),
                                'removed' => array_keys(array_diff_key(
                                    Arr::dot($actualKeys),
                                    Arr::dot($expectedKeys)
                                )),
                            ],
                        ];
                    })
                    ->tap(function (Collection $files) use ($locale) {
                        $missingKeysCount = $files->sum(fn ($file): int => count($file['missing']));
                        $removedKeysCount = $files->sum(fn ($file): int => count($file['removed']));

                        $locale = locale_get_display_name($locale, 'en');

                        if ($missingKeysCount == 0 && $removedKeysCount == 0) {
                            $this->info("[✓] Found no missing or removed translation keys for {$locale}!\n");

                            $this->newLine();
                        } elseif ($missingKeysCount > 0 && $removedKeysCount > 0) {
                            $this->warn("[!] Found {$missingKeysCount} missing translation " . Str::plural('key', $missingKeysCount) . " and {$removedKeysCount} removed translation " . Str::plural('key', $removedKeysCount) . " for {$locale}.\n");
                        } elseif ($missingKeysCount > 0) {
                            $this->warn("[!] Found {$missingKeysCount} missing translation " . Str::plural('key', $missingKeysCount) . " for {$locale}.\n");
                        } elseif ($removedKeysCount > 0) {
                            $this->warn("[!] Found {$removedKeysCount} removed translation " . Str::plural('key', $removedKeysCount) . " for {$locale}.\n");
                        }
                    })
                    ->filter(static fn ($keys): bool => count($keys['missing']) || count($keys['removed']))
                    ->each(function ($keys, string $file) {
                        $this->table(
                            [$file, ''],
                            [
                                ...array_map(fn (string $key): array => [$key, 'Missing'], $keys['missing']),
                                ...array_map(fn (string $key): array => [$key, 'Removed'], $keys['removed']),
                            ],
                            'box',
                        );

                        $this->newLine();
                    });

            });

        return self::SUCCESS;
    }
}
