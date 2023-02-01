<?php

namespace App\Http\Controllers\Api\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Resources\Workspaces\WorkspaceResource;
use App\Models\Workspace;

class WorkspaceController extends Controller
{
    public function getList()
    {
        $workspaces = Workspace::query()
            ->orderBy('title', 'asc')
            ->get();

        return WorkspaceResource::collection($workspaces);
    }
}
