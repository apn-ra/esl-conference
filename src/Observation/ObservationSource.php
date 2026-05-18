<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Observation;

enum ObservationSource
{
    case Event;
    case CommandReply;
    case Replay;
}
