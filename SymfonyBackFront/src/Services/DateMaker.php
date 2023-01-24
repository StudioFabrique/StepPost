<?php

namespace App\Services;

use DateTime;
use DateTimeZone;

/**
 * Service pour générer et convertir la date
 */
class DateMaker
{
    public function createFromDateTimeZone($date = 'now', string $zone = "UTC"): ?DateTime
    {
        return new DateTime($date, new DateTimeZone($zone));
    }

    public function convertDateDefault($date = null): ?DateTime
    {
        return $date != null ? date_modify($date, '+1 hour') : null;
    }
}
