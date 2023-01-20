<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BootstrapResource;

class BootstrapController extends Controller
{
    public function getBootstrap()
    {
        return new BootstrapResource(null);
    }
}
