<?php

namespace App\Services;

use DateTime;
use DateTimeZone;

/**
 * Service pour générer et convertir la date
 */
class DateMaker
{
    public function createFromDateTimeZone($date = 'now', string $zone = "EU"): ?DateTime
    {
        return new DateTime($date, new DateTimeZone($zone));
    }

    public function convertDateDefault($date = null): ?DateTime
    {
        $date = $date != null ? date_create($date) : null;
        return $date;
    }
}
