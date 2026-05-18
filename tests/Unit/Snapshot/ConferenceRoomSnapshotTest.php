<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Snapshot;

use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Snapshot\ConferenceRoomSnapshot;
use PHPUnit\Framework\TestCase;

final class ConferenceRoomSnapshotTest extends TestCase
{
    public function testSerializesDeterministically(): void
    {
        $snapshot = new ConferenceRoomSnapshot(ConferenceName::fromObserved('support-1001@default'), 0);

        self::assertSame([
            'conference_name' => 'support-1001@default',
            'reported_member_count' => 0,
            'members' => [],
            'blockers' => [],
        ], $snapshot->toArray());
        self::assertTrue($snapshot->isComplete());
    }
}
