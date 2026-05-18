<?php

declare(strict_types=1);

namespace Apntalk\EslConference\Observation;

enum ObservationConfidence
{
    case Observed;
    case Partial;
    case Unknown;
}
