<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Boundary;

use PHPUnit\Framework\TestCase;

final class NoFrameworkDependencyTest extends TestCase
{
    public function testComposerDoesNotRequireFrameworkPackages(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        $packages = array_keys(array_merge(
            is_array($composer['require'] ?? null) ? $composer['require'] : [],
            is_array($composer['require-dev'] ?? null) ? $composer['require-dev'] : [],
        ));
        $joined = strtolower(implode(' ', $packages));

        foreach (['laravel', 'illuminate', 'eloquent', 'doctrine/dbal', 'react/event-loop'] as $term) {
            self::assertStringNotContainsString($term, $joined);
        }
    }
}
