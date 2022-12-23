<?php

namespace App\Services;

use DateTime;
use DateTimeZone;

class DateMaker
{
    public function createFromDateTimeZone($date = 'now', string $zone = "UTC"): ?DateTime
    {
        return new DateTime($date, new DateTimeZone($zone));
    }

    public function convertDateDefault($date = null): ?DateTime
    {
        $date = $date != null ? date_create($date) : null;
        return $date;
    }
}
