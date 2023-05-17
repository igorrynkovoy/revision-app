<?php

namespace App\Repositories\Interfaces;

use App\Models\Workspace;

interface WorkspaceRepositoryInterface
{
    public function all();

    public function getById($id): Workspace;
}
