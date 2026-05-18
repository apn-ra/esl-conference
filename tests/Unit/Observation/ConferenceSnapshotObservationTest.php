<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Unit\Observation;

use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Observation\ConferenceObservationFactory;
use Apntalk\EslConference\Parser\ConferenceListReplyParser;
use Apntalk\EslConference\Tests\Support\FixtureLoader;
use PHPUnit\Framework\TestCase;

final class ConferenceSnapshotObservationTest extends TestCase
{
    public function testCreatesSnapshotObservation(): void
    {
        $result = (new ConferenceListReplyParser())->parse(
            FixtureLoader::text('replies/conference_list_members.txt'),
            conferenceName: ConferenceName::fromObserved('support-1001'),
        );
        self::assertNotNull($result->snapshot);

        $observation = (new ConferenceObservationFactory())->fromSnapshot($result->snapshot);

        self::assertSame('snapshot', $observation->type);
        self::assertNull($observation->toArray()['snapshot']['reported_member_count']);
        self::assertCount(1, $observation->toArray()['snapshot']['members']);
    }
}
