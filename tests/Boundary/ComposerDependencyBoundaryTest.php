<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Boundary;

use PHPUnit\Framework\TestCase;

final class ComposerDependencyBoundaryTest extends TestCase
{
    public function testBaseDependenciesStayNarrow(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        $require = $composer['require'] ?? [];
        self::assertIsArray($require);
        self::assertSame('apntalk/esl-conference', $composer['name'] ?? null);
        self::assertArrayHasKey('php', $require);
        self::assertArrayHasKey('apntalk/esl-core', $require);
        self::assertCount(2, $require);
    }

    public function testRuntimeFrameworkAndReplayDependenciesStayOutOfRequireAndRequireDev(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);

        $dependencySets = [
            'require' => is_array($composer['require'] ?? null) ? $composer['require'] : [],
            'require-dev' => is_array($composer['require-dev'] ?? null) ? $composer['require-dev'] : [],
        ];

        $forbiddenPackages = [
            'apntalk/esl-react',
            'apntalk/esl-replay',
            'laravel/framework',
            'doctrine/dbal',
            'react/event-loop',
        ];

        foreach ($dependencySets as $section => $packages) {
            foreach ($forbiddenPackages as $package) {
                self::assertArrayNotHasKey($package, $packages, sprintf('%s must not require %s', $section, $package));
            }

            foreach (array_keys($packages) as $package) {
                self::assertFalse(str_starts_with($package, 'illuminate/'), sprintf('%s must not require Illuminate packages', $section));
            }
        }
    }

    public function testStableDocsKeepExceptionsIsolated(): void
    {
        $root = dirname(__DIR__, 2);
        $allowed = [
            realpath($root . '/docs/package-boundaries.md'),
            realpath($root . '/docs/downstream-apntalk-integration.md'),
            realpath($root . '/docs/implementation-plan.md'),
        ];
        $forbidden = ['tenant', 'provider_binding', 'sip_account', 'campaign', 'lead', 'conferenceReady', 'callReady', 'Laravel', 'Eloquent', 'database', 'worker', 'supervisor'];
        $violations = [];

        foreach (glob($root . '/docs/*.md') ?: [] as $path) {
            if (in_array(realpath($path), $allowed, true)) {
                continue;
            }

            $contents = (string) file_get_contents($path);
            foreach ($forbidden as $term) {
                if (stripos($contents, $term) !== false) {
                    $violations[] = sprintf('%s contains %s', $path, $term);
                }
            }
        }

        self::assertSame([], $violations);
    }
}
