<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Model;

use Apntalk\EslConference\Exception\UnsafeConferenceCommandArgument;
use Apntalk\EslConference\Model\ConferenceCommandToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConferenceCommandTokenTest extends TestCase
{
    public function testAcceptsSafeToken(): void
    {
        self::assertSame('support-1001@default', ConferenceCommandToken::fromString('support-1001@default')->raw());
    }

    #[DataProvider('unsafeTokens')]
    public function testRejectsUnsafeToken(string $token): void
    {
        $this->expectException(UnsafeConferenceCommandArgument::class);

        ConferenceCommandToken::fromString($token);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unsafeTokens(): iterable
    {
        yield 'empty' => [''];
        yield 'line-feed' => ["room\nother"];
        yield 'carriage-return' => ["room\rother"];
        yield 'nul' => ["room\0other"];
        yield 'space' => ['room list'];
        yield 'separator' => ['room;list'];
        yield 'unbounded' => [str_repeat('a', 257)];
    }
}
