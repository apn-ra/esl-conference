<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceName
{
    private function __construct(private string $raw)
    {
    }

    public static function fromString(string $raw): self
    {
        return new self(ConferenceCommandToken::fromString($raw)->raw());
    }

    public static function fromObserved(string $raw): self
    {
        return new self($raw);
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function hasProfileCandidate(): bool
    {
        return substr_count($this->raw, '@') === 1 && $this->profileNameCandidate() !== null;
    }

    public function profileNameCandidate(): ?string
    {
        $parts = explode('@', $this->raw, 2);

        if (count($parts) !== 2 || $parts[1] === '') {
            return null;
        }

        return $this->isConservativeCandidate($parts[1]) ? $parts[1] : null;
    }

    public function roomNameCandidate(): ?string
    {
        $room = explode('@', $this->raw, 2)[0];

        if ($room === '') {
            return null;
        }

        return $this->isConservativeCandidate($room) ? $room : null;
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'raw' => $this->raw,
            'room_name_candidate' => $this->roomNameCandidate(),
            'profile_name_candidate' => $this->profileNameCandidate(),
        ];
    }

    private function isConservativeCandidate(string $candidate): bool
    {
        return preg_match('/^[A-Za-z0-9._:-]+$/', $candidate) === 1;
    }
}
