<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Tests\Live;

use Apntalk\EslConference\Event\ConferenceMaintenanceEventFactory;
use Apntalk\EslConference\Event\ConferenceMemberJoinedEvent;
use Apntalk\EslConference\Event\ConferenceMemberLeftEvent;
use Apntalk\EslConference\Model\ConferenceName;
use Apntalk\EslConference\Observation\ConferenceObservationFactory;
use Apntalk\EslConference\Parser\ConferenceListReplyParser;
use PHPUnit\Framework\TestCase;

final class FreeSwitchConferenceLiveValidationTest extends TestCase
{
    public function testConferenceMaintenanceAndListReplyValidateAgainstLiveFreeSwitch(): void
    {
        if (getenv('ESL_CONFERENCE_LIVE_TEST') !== '1') {
            self::markTestSkipped('Live FreeSWITCH validation is operator-run only.');
        }

        $host = getenv('ESL_CONFERENCE_LIVE_HOST') ?: '::1';
        $port = (int) (getenv('ESL_CONFERENCE_LIVE_PORT') ?: '8021');
        $password = getenv('ESL_CONFERENCE_LIVE_PASSWORD') ?: 'ClueCon';
        $conference = getenv('ESL_CONFERENCE_LIVE_CONFERENCE') ?: 'support-1001';

        $events = $this->connect($host, $port, $password);
        $control = $this->connect($host, $port, $password);

        $this->send($events, 'event plain ALL');
        $this->readMessage($events);

        $version = $this->api($control, 'version');
        self::assertStringContainsString('FreeSWITCH Version', $version);
        self::assertSame('true', trim($this->api($control, 'module_exists mod_conference')));

        $this->api($control, sprintf('conference %s hup all', $conference));

        $dialReply = $this->api($control, sprintf(
            "conference %s dial loopback/9196 1001 'Support 1001'",
            $conference,
        ), 20);
        self::assertStringContainsString('+OK', $dialReply);

        $joinedHeaders = $this->waitForMaintenanceEvent($events, 'add-member', $conference, 20);
        $joinedResult = (new ConferenceMaintenanceEventFactory())->parse($joinedHeaders);
        self::assertInstanceOf(ConferenceMemberJoinedEvent::class, $joinedResult->event);

        $joinedObservation = (new ConferenceObservationFactory())->fromEvent($joinedResult->event);
        self::assertSame('member_joined', $joinedObservation->type);

        $listReply = $this->waitForListReply($control, $conference);
        $listResult = (new ConferenceListReplyParser())->parse(
            $listReply,
            conferenceName: ConferenceName::fromObserved($conference),
        );
        self::assertContains($listResult->status, ['ok', 'partial']);
        self::assertNotNull($listResult->snapshot);
        self::assertNotSame([], $listResult->snapshot->members);

        $rejectedReply = $this->api($control, sprintf('conference %s-missing list', $conference));
        self::assertStringStartsWith('-ERR', trim($rejectedReply));

        $this->api($control, sprintf('conference %s hup all', $conference));
        $leftHeaders = $this->waitForMaintenanceEvent($events, 'del-member', $conference, 20);
        $leftResult = (new ConferenceMaintenanceEventFactory())->parse($leftHeaders);
        self::assertInstanceOf(ConferenceMemberLeftEvent::class, $leftResult->event);

        $leftObservation = (new ConferenceObservationFactory())->fromEvent($leftResult->event);
        self::assertSame('member_left', $leftObservation->type);

        if (getenv('ESL_CONFERENCE_CAPTURE_FIXTURES') === '1') {
            $this->writeFixture('events/conference_member_joined.event', $this->renderEventFixture($joinedHeaders, 'add-member'));
            $this->writeFixture('events/conference_member_left.event', $this->renderEventFixture($leftHeaders, 'del-member'));
            $this->writeFixture('replies/conference_list_members.txt', $this->sanitizeListReply($listReply));
            $this->writeFixture('replies/conference_rejected.txt', $this->sanitizeRejectedReply($rejectedReply));
        }
    }

    /**
     * @return resource
     */
    private function connect(string $host, int $port, string $password)
    {
        $target = str_contains($host, ':') ? sprintf('tcp://[%s]:%d', $host, $port) : sprintf('tcp://%s:%d', $host, $port);
        $socket = stream_socket_client($target, $errno, $error, 5);

        if ($socket === false) {
            self::fail(sprintf('Could not connect to ESL: %s %s', $errno, $error));
        }

        stream_set_timeout($socket, 5);

        $hello = $this->readMessage($socket);
        self::assertSame('auth/request', $hello['headers']['Content-Type'] ?? null);

        $this->send($socket, sprintf('auth %s', $password));
        $auth = $this->readMessage($socket);
        self::assertStringContainsString('OK', ($auth['headers']['Reply-Text'] ?? '') . $auth['body']);

        return $socket;
    }

    /**
     * @param resource $socket
     */
    private function api($socket, string $command, int $timeout = 5): string
    {
        stream_set_timeout($socket, $timeout);
        $this->send($socket, sprintf('api %s', $command));

        do {
            $message = $this->readMessage($socket, $timeout);
        } while (($message['headers']['Content-Type'] ?? null) !== 'api/response');

        return $message['body'];
    }

    /**
     * @param resource $socket
     */
    private function send($socket, string $command): void
    {
        fwrite($socket, $command . "\n\n");
    }

    /**
     * @param resource $socket
     * @return array{headers: array<string, string>, body: string}
     */
    private function readMessage($socket, int $timeout = 5, bool $failOnTimeout = true): array
    {
        stream_set_timeout($socket, $timeout);

        $headers = [];
        while (($line = fgets($socket)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                break;
            }

            [$name, $value] = array_pad(explode(':', $line, 2), 2, '');
            $headers[$name] = urldecode(ltrim($value));
        }

        if ($headers === []) {
            if ($failOnTimeout) {
                self::fail('Timed out waiting for an ESL message.');
            }

            return ['headers' => [], 'body' => ''];
        }

        $length = (int) ($headers['Content-Length'] ?? 0);
        $body = '';
        while (strlen($body) < $length) {
            $chunk = fread($socket, $length - strlen($body));
            if ($chunk === false || $chunk === '') {
                break;
            }

            $body .= $chunk;
        }

        foreach (preg_split('/\R/', trim($body)) ?: [] as $bodyLine) {
            if (! str_contains($bodyLine, ':')) {
                continue;
            }

            [$name, $value] = array_pad(explode(':', $bodyLine, 2), 2, '');
            $headers[$name] = urldecode(ltrim($value));
        }

        return ['headers' => $headers, 'body' => $body];
    }

    /**
     * @param resource $socket
     * @return array<string, string>
     */
    private function waitForMaintenanceEvent($socket, string $action, string $conference, int $timeout): array
    {
        $deadline = time() + $timeout;
        $seen = [];

        do {
            $message = $this->readMessage($socket, max(1, $deadline - time()), false);
            $headers = $message['headers'];
            if ($headers === []) {
                continue;
            }
            $seen[] = sprintf(
                '%s/%s/%s/%s',
                $headers['Event-Name'] ?? '-',
                $headers['Event-Subclass'] ?? '-',
                $headers['Action'] ?? '-',
                $headers['Conference-Name'] ?? '-',
            );

            if (
                ($headers['Event-Subclass'] ?? null) === 'conference::maintenance'
                && ($headers['Action'] ?? null) === $action
                && ($headers['Conference-Name'] ?? null) === $conference
            ) {
                return $headers;
            }
        } while (time() < $deadline);

        self::fail(sprintf('Timed out waiting for %s on %s. Seen: %s', $action, $conference, implode(', ', array_slice($seen, -12))));
    }

    /**
     * @param resource $socket
     */
    private function waitForListReply($socket, string $conference): string
    {
        $deadline = time() + 10;

        do {
            $reply = trim($this->api($socket, sprintf('conference %s list', $conference)));
            if ($reply !== '' && ! str_starts_with($reply, '-ERR')) {
                return $reply;
            }

            usleep(250_000);
        } while (time() < $deadline);

        self::fail(sprintf('Timed out waiting for a list reply from %s.', $conference));
    }

    private function writeFixture(string $relativePath, string $contents): void
    {
        $path = dirname(__DIR__) . '/Fixture/' . $relativePath;
        file_put_contents($path, rtrim($contents) . "\n");
    }

    /**
     * @param array<string, string> $headers
     */
    private function renderEventFixture(array $headers, string $action): string
    {
        $fixed = [
            'Event-Name' => 'CUSTOM',
            'Event-Subclass' => 'conference::maintenance',
            'Action' => $action,
            'Conference-Name' => 'support-1001',
            'Member-ID' => '1',
            'Channel-Name' => 'loopback/9196-a',
            'Unique-ID' => '11111111-1111-4111-8111-111111111111',
            'Caller-Caller-ID-Number' => '9196',
            'Caller-Caller-ID-Name' => 'Outbound Call',
            'Caller-Destination-Number' => '9196',
            'Caller-Username' => '9196',
            'Caller-Context' => 'default',
        ];

        $order = [
            'Event-Name',
            'Event-Subclass',
            'Action',
            'Conference-Name',
            'Conference-Profile-Name',
            'Conference-Size',
            'Member-ID',
            'Member-Type',
            'Channel-Name',
            'Unique-ID',
            'Caller-Caller-ID-Number',
            'Caller-Caller-ID-Name',
            'Caller-Destination-Number',
            'Caller-Username',
            'Caller-Context',
        ];

        $lines = [];
        foreach ($order as $name) {
            if (array_key_exists($name, $fixed)) {
                $lines[] = sprintf('%s: %s', $name, $fixed[$name]);
                continue;
            }

            if (isset($headers[$name])) {
                $lines[] = sprintf('%s: %s', $name, $headers[$name]);
            }
        }

        return implode("\n", $lines);
    }

    private function sanitizeListReply(string $reply): string
    {
        $lines = [];
        foreach (preg_split('/\R/', trim($reply)) ?: [] as $line) {
            $parts = array_map('trim', explode(';', $line));
            if (count($parts) >= 6) {
                $parts[0] = (string) (count($lines) + 1);
                $parts[1] = 'loopback/9196-a';
                $parts[2] = sprintf('%d1111111-1111-4111-8111-111111111111', count($lines) + 1);
                $parts[3] = 'Outbound Call';
                $parts[4] = '9196';
            }

            $lines[] = implode(';', $parts);
        }

        return implode("\n", $lines);
    }

    private function sanitizeRejectedReply(string $reply): string
    {
        return preg_replace('/Conference\s+\S+\s+not found/', 'Conference support-1001-missing not found', trim($reply)) ?? trim($reply);
    }
}
