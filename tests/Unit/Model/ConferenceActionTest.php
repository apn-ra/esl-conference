<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Model;

use Apntalk\EslConference\Model\ConferenceAction;
use PHPUnit\Framework\TestCase;

final class ConferenceActionTest extends TestCase
{
    public function testClassifiesStableActions(): void
    {
        self::assertTrue(ConferenceAction::fromString('add-member')?->isAddMember());
        self::assertTrue(ConferenceAction::fromString('del-member')?->isDelMember());
        self::assertNull(ConferenceAction::fromString(''));
    }
}
