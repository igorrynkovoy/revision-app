<?php

namespace App\Http\Controllers\Api\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\CreateRequest;
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

    public function getDetails(Workspace $workspace)
    {
        return new WorkspaceResource($workspace);
    }

    public function postCreate(CreateRequest $request)
    {
        $workspace = new Workspace();
        $workspace->title = $request->get('title');
        $workspace->save();

        return new WorkspaceResource($workspace);
    }

    public function postEdit(Workspace $workspace, CreateRequest $request)
    {
        $workspace->title = $request->get('title');
        $workspace->save();

        return new WorkspaceResource($workspace);
    }
}

