<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Dates
 * @package App\Facades
 */
class Dates extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Helpers\Dates::class;
    }
}
