<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Event;

final readonly class ConferenceEventFieldExtractor
{
    /**
     * @return array<string, string>
     */
    /**
     * @param object|array<string, string> $event
     * @return array<string, string>
     */
    public function extract(object|array $event): array
    {
        if (is_array($event)) {
            /** @var array<string, string> $headers */
            $headers = array_filter($event, 'is_string');

            return $headers;
        }

        if (method_exists($event, 'headers')) {
            $headers = $event->headers();

            if (is_array($headers)) {
                /** @var array<string, string> $filtered */
                $filtered = array_filter($headers, 'is_string');

                return $filtered;
            }
        }

        return [];
    }

    /**
     * @param array<string, string> $headers
     */
    public function get(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return null;
    }
}
