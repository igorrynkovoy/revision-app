<?php

namespace App\Repositories;

use App\Models\Workspace;
use App\Repositories\Interfaces\WorkspaceRepositoryInterface;

class WorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function all()
    {
        return Workspace::query()
            ->orderBy('title', 'asc')
            ->get();
    }

    public function getById($id): Workspace
    {
        return Workspace::find($id);
    }
}
