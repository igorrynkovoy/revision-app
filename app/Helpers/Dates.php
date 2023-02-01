<?php

namespace App\Helpers;

use Carbon\Carbon;

class Dates
{
    /**
     * @decription Returns timestamp of a date
     */
    public function toTimestamp(mixed $date): null|int
    {
        if ($date instanceof Carbon) {
            return $date->timestamp;
        }

        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->timestamp;
        } catch (\Throwable) {
            return null;
        }
    }
}
