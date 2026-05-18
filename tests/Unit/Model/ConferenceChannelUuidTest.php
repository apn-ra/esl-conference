<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Model;

use Apntalk\EslConference\Model\ConferenceChannelUuid;
use PHPUnit\Framework\TestCase;

final class ConferenceChannelUuidTest extends TestCase
{
    public function testAcceptsRepresentativeUuidValue(): void
    {
        $uuid = ConferenceChannelUuid::fromString('11111111-1111-4111-8111-111111111111');

        self::assertSame('11111111-1111-4111-8111-111111111111', $uuid?->raw());
        self::assertNull(ConferenceChannelUuid::fromString(''));
    }
}
