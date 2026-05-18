<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Model;

use Apntalk\EslConference\Model\ConferenceMemberId;
use PHPUnit\Framework\TestCase;

final class ConferenceMemberIdTest extends TestCase
{
    public function testAcceptsNumericMemberId(): void
    {
        self::assertSame(7, ConferenceMemberId::fromString('7')?->toInt());
        self::assertNull(ConferenceMemberId::fromString('abc'));
    }
}
