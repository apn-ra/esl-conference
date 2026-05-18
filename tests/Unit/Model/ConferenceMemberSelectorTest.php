<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Model;

use Apntalk\EslConference\Model\ConferenceMemberId;
use Apntalk\EslConference\Model\ConferenceMemberSelector;
use PHPUnit\Framework\TestCase;

final class ConferenceMemberSelectorTest extends TestCase
{
    public function testBuildsSelectorFromMemberId(): void
    {
        $memberId = ConferenceMemberId::fromString('7');

        self::assertNotNull($memberId);
        self::assertSame('7', ConferenceMemberSelector::fromMemberId($memberId)->raw());
    }
}
