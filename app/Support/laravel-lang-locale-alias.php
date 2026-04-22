<?php

declare(strict_types=1);

/**
 * Alias para compatibilidad con Laravel 12.
 * El enum de locales en laravel-lang sigue en LaravelLang\LocaleList\Locale;
 * este autoload permite usar el namespace LaravelLang\Locales\Enums\Locale.
 */
spl_autoload_register(static function (string $class): bool {
    if ($class !== 'LaravelLang\\Locales\\Enums\\Locale') {
        return false;
    }
    if (! class_exists(\LaravelLang\LocaleList\Locale::class)) {
        return false;
    }
    class_alias(\LaravelLang\LocaleList\Locale::class, 'LaravelLang\\Locales\\Enums\\Locale');

    return true;
}, prepend: true);
