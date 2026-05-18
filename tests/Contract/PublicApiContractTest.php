<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Contract;

use Apntalk\EslConference\Command\ConferenceCommand;
use Apntalk\EslConference\Command\ConferenceListMembersCommand;
use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Parser\ConferenceListReplyParser;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;
use PHPUnit\Framework\TestCase;

final class PublicApiContractTest extends TestCase
{
    public function testStableTypesAreAvailable(): void
    {
        self::assertInstanceOf(ConferenceCommand::class, ConferenceListMembersCommand::forConference(ConferenceName::fromString('support-1001@default')));
        self::assertInstanceOf(ConferenceMaintenanceEventFactory::class, new ConferenceMaintenanceEventFactory());
        self::assertInstanceOf(ConferenceListReplyParser::class, new ConferenceListReplyParser());
        self::assertSame('conference_reply_unparseable', ConferenceBlocker::ConferenceReplyUnparseable->value);
    }

    public function testStableBlockerValuesAreProtected(): void
    {
        self::assertSame([
            'conference_name_missing',
            'conference_event_subclass_mismatch',
            'conference_action_missing',
            'conference_action_unknown',
            'conference_member_id_missing',
            'conference_channel_uuid_missing',
            'conference_reply_unparseable',
            'conference_reply_unsupported',
            'conference_member_not_found',
            'conference_command_rejected',
            'conference_observation_incomplete',
            'conference_snapshot_unsupported',
            'conference_command_argument_unsafe',
            'conference_vocabulary_unproven',
        ], array_map(
            static fn (ConferenceBlocker $blocker): string => $blocker->value,
            ConferenceBlocker::cases(),
        ));
    }
}
