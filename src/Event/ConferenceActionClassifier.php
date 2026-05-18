<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Event;

use Apntalk\EslConference\Model\ConferenceAction;

final readonly class ConferenceActionClassifier
{
    public function classify(ConferenceAction $action): string
    {
        if ($action->isAddMember()) {
            return ConferenceAction::ADD_MEMBER;
        }

        if ($action->isDelMember()) {
            return ConferenceAction::DEL_MEMBER;
        }

        return 'unknown';
    }
}
