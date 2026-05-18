<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Boundary;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class NoRuntimeOwnershipVocabularyTest extends TestCase
{
    public function testSourceDoesNotOwnLiveRuntimeBehavior(): void
    {
        $forbidden = ['open socket', 'reconnect', 'supervisor', 'service provider', 'event loop'];
        $violations = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/src'));

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = strtolower((string) file_get_contents($file->getPathname()));

            foreach ($forbidden as $term) {
                if (str_contains($contents, $term)) {
                    $violations[] = sprintf('%s contains %s', $file->getPathname(), $term);
                }
            }
        }

        self::assertSame([], $violations);
    }
}
