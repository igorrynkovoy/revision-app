<?php

namespace App\Http\Controllers\Api\Workspace;

use App\Events\Workspace\Board\Created;
use App\Events\Workspace\Board\Updated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\Board\CreateRequest;
use App\Http\Requests\Workspace\Board\UpdateRequest;
use App\Http\Resources\Workspaces\Boards\BoardResource;
use App\Models\Workspace;
use Illuminate\Http\Request;
use function event;

class BoardsController extends Controller
{
    public function getList(Workspace $workspace, Request $request)
    {
        $limit = min(10, $request->get('limit', 10));
        $page = max(1, $request->get('page', 1));

        $boards = $workspace->boards()
            ->forPage($page, $limit)
            ->orderBy('starred', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return BoardResource::collection($boards);
    }

    public function getBoard(Workspace $workspace, Workspace\Board\Board $board)
    {
        return (new BoardResource($board));
    }

    public function postCreate(Workspace $workspace, CreateRequest $request)
    {
        $board = new Workspace\Board\Board();
        $board->workspace_id = $workspace->id;
        $board->starred = false;
        $board->title = $request->get('title');
        $board->save();

        event(new Created($board));

        return (new BoardResource($board));
    }

    public function postUpdate(Workspace $workspace, Workspace\Board\Board $board, UpdateRequest $request)
    {
        $board->title = $request->get('title');
        $board->starred = $request->get('starred');
        $board->save();

        event(new Updated($board));

        return (new BoardResource($board));
    }
}
