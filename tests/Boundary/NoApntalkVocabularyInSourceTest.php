<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Boundary;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class NoApntalkVocabularyInSourceTest extends TestCase
{
    public function testSourceDoesNotContainDownstreamVocabulary(): void
    {
        $forbidden = [
            'tenant',
            'tenant_id',
            'provider_binding',
            'providerBinding',
            'provider_binding_id',
            'sip_account',
            'sipAccount',
            'campaign',
            'lead',
            'conferenceReady',
            'callReady',
            'canonical lifecycle',
            'lifecycle snapshot',
        ];

        $violations = $this->scan(dirname(__DIR__, 2) . '/src', $forbidden);

        self::assertSame([], $violations);
    }

    /**
     * @param list<string> $terms
     * @return list<string>
     */
    private function scan(string $path, array $terms): array
    {
        $violations = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);

            foreach ($terms as $term) {
                if (stripos($contents, $term) !== false) {
                    $violations[] = sprintf('%s contains %s', $file->getPathname(), $term);
                }
            }
        }

        return $violations;
    }
}
