<?php

namespace App\Providers;

use App\Repositories;
use App\Repositories\Interfaces as RepositoryInterfaces;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(RepositoryInterfaces\WorkspaceRepositoryInterface::class, Repositories\WorkspaceRepository::class);
        $this->registerLitecoin();
    }

    private function registerLitecoin()
    {
        $this->app->bind(RepositoryInterfaces\Blockchain\Litecoin\AddressRepositoryInterface::class, Repositories\Blockchain\Litecoin\AddressRepository::class);
    }

}
