<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Support;

final class FixtureLoader
{
    public static function path(string $relative): string
    {
        return dirname(__DIR__) . '/Fixture/' . $relative;
    }

    public static function text(string $relative): string
    {
        $contents = file_get_contents(self::path($relative));

        if ($contents === false) {
            throw new \RuntimeException(sprintf('Fixture not readable: %s', $relative));
        }

        return $contents;
    }

    /**
     * @return array<string, string>
     */
    public static function eventHeaders(string $relative): array
    {
        $headers = [];

        foreach (preg_split('/\R/', self::text($relative)) ?: [] as $line) {
            if (trim($line) === '' || str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
            $headers[trim($key)] = ltrim($value);
        }

        return $headers;
    }
}
