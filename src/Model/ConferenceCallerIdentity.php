<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Model;

final readonly class ConferenceCallerIdentity
{
    public function __construct(
        public ?string $callerNumber,
        public ?string $callerName,
        public ?string $destinationNumber,
        public ?string $username,
        public ?string $context,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'caller_number' => $this->callerNumber,
            'caller_name' => $this->callerName,
            'destination_number' => $this->destinationNumber,
            'username' => $this->username,
            'context' => $this->context,
        ];
    }
}
