<?php

declare(strict_types=1);

namespace JeffersonGoncalves\LocaleCookie;

use Illuminate\Support\Facades\App;

class LocaleCookie
{
    /**
     * Reduce a locale to its short language code (`pt_BR`/`pt-BR`/`pt` → `pt`).
     * Defaults to the application's current locale. Falls back to `en` when the
     * locale has no usable prefix.
     */
    public static function short(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        $base = strtolower(preg_split('/[_-]/', $locale)[0] ?? '');

        return $base !== '' ? $base : 'en';
    }
}
