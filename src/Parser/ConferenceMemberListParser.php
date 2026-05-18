<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Parser;

use Apntalk\EslConference\Model\ConferenceCallerIdentity;
use Apntalk\EslConference\Model\ConferenceChannelIdentity;
use Apntalk\EslConference\Model\ConferenceChannelUuid;
use Apntalk\EslConference\Model\ConferenceMemberId;
use Apntalk\EslConference\Model\ConferenceMemberIdentity;
use Apntalk\EslConference\Model\ConferenceMemberState;
use Apntalk\EslConference\Snapshot\ConferenceMemberSnapshot;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceMemberListParser
{
    public function parseLine(string $line, ParseMode $mode = ParseMode::Lenient): ?ConferenceMemberSnapshot
    {
        $parts = array_map('trim', explode(';', $line));
        if (count($parts) < 6) {
            return null;
        }

        [$memberIdRaw, $channelName, $channelUuidRaw, $callerName, $callerNumber, $flagsRaw] = $parts;
        $blockers = [];
        $memberId = ConferenceMemberId::fromString($memberIdRaw);
        $channelUuid = ConferenceChannelUuid::fromString($channelUuidRaw);

        if ($memberId === null) {
            $blockers[] = ConferenceBlocker::ConferenceMemberIdMissing;
        }

        if ($channelUuid === null) {
            $blockers[] = ConferenceBlocker::ConferenceChannelUuidMissing;
        }

        if ($mode === ParseMode::Strict && $blockers !== []) {
            return null;
        }

        return new ConferenceMemberSnapshot(
            new ConferenceMemberIdentity(
                $memberId,
                null,
                new ConferenceChannelIdentity($channelName === '' ? null : $channelName, $channelUuid),
                new ConferenceCallerIdentity(
                    $callerNumber === '' ? null : $callerNumber,
                    $callerName === '' ? null : $callerName,
                    null,
                    null,
                    null,
                ),
            ),
            new ConferenceMemberState($flagsRaw === '' ? [] : array_values(array_filter(explode('|', $flagsRaw)))),
            $blockers,
        );
    }
}
