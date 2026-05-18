<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceAction
{
    public const string ADD_MEMBER = 'add-member';
    public const string DEL_MEMBER = 'del-member';

    private function __construct(private string $raw)
    {
    }

    public static function fromString(string $raw): ?self
    {
        $value = trim($raw);

        if ($value === '') {
            return null;
        }

        return new self($value);
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function isAddMember(): bool
    {
        return $this->raw === self::ADD_MEMBER;
    }

    public function isDelMember(): bool
    {
        return $this->raw === self::DEL_MEMBER;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['raw' => $this->raw];
    }
}
