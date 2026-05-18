<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Parser;

use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Reply\ConferenceCommandParseResult;
use Apntalk\EslConference\Snapshot\ConferenceMemberSnapshot;
use Apntalk\EslConference\Snapshot\ConferenceRoomSnapshot;
use Apntalk\EslConference\Vocabulary\ConferenceBlocker;

final readonly class ConferenceListReplyParser
{
    public function __construct(private ConferenceMemberListParser $members = new ConferenceMemberListParser())
    {
    }

    public function parse(
        string $reply,
        ParseMode $mode = ParseMode::Lenient,
        ?ConferenceName $conferenceName = null,
    ): ConferenceCommandParseResult {
        $text = trim($reply);

        if ($text === '') {
            return ConferenceCommandParseResult::failed([ConferenceBlocker::ConferenceReplyUnparseable], 'empty reply');
        }

        if (str_starts_with($text, '-ERR')) {
            return ConferenceCommandParseResult::failed([ConferenceBlocker::ConferenceCommandRejected], $this->summary($text));
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $text) ?: [])));
        $header = $lines[0] ?? '';

        if (preg_match('/^(?:\+OK\s+)?Conference\s+(\S+)\s+\((\d+)\s+members?(?:\s+[^)]*)?\)$/', $header, $matches) === 1) {
            $roomName = ConferenceName::fromObserved($matches[1]);
            $reportedCount = (int) $matches[2];
            $memberLines = array_slice($lines, 1);
        } elseif ($conferenceName !== null) {
            $roomName = $conferenceName;
            $reportedCount = null;
            $memberLines = $lines;
        } else {
            $blocker = $mode === ParseMode::Strict ? ConferenceBlocker::ConferenceReplyUnsupported : ConferenceBlocker::ConferenceReplyUnparseable;

            return ConferenceCommandParseResult::unknownFormat([$blocker], $this->summary($text));
        }

        $memberSnapshots = [];
        $blockers = [];

        foreach ($memberLines as $line) {
            $member = $this->members->parseLine($line, $mode);

            if ($member === null) {
                $blockers[] = ConferenceBlocker::ConferenceReplyUnsupported;
                continue;
            }

            $memberSnapshots[] = $member;
            array_push($blockers, ...$member->blockers);
        }

        if ($mode === ParseMode::Strict && $blockers !== []) {
            return ConferenceCommandParseResult::failed(array_values(array_unique($blockers, SORT_REGULAR)), 'strict reply parse failed');
        }

        $snapshot = new ConferenceRoomSnapshot($roomName, $reportedCount, $memberSnapshots, array_values(array_unique($blockers, SORT_REGULAR)));

        if ($snapshot->blockers !== []) {
            return ConferenceCommandParseResult::partial($snapshot, $snapshot->blockers);
        }

        return ConferenceCommandParseResult::success($snapshot);
    }

    private function summary(string $text): string
    {
        return substr(preg_replace('/\s+/', ' ', $text) ?? $text, 0, 160);
    }
}
