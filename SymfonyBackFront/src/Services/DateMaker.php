<?php

namespace App\Services;

use DateTime;

class DateMaker
{
    function convertDateDefault($date = null): ?DateTime
    {
        $date = $date != null ? date_create($date) : null;
        return $date;
    }
}
