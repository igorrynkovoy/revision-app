<?php

namespace App\Http\Controllers\Api\Workspace\Boards;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\Board\Layout\CreateRequest;
use App\Http\Requests\Workspace\Board\Layout\ListRequest;
use App\Http\Requests\Workspace\Board\Layout\UpdateRequest;
use App\Http\Resources\Workspaces\Boards\Layouts\LayoutLightResource;
use App\Http\Resources\Workspaces\Boards\Layouts\LayoutResource;
use App\Models\Workspace;

class LayoutsController extends Controller
{
    public function getList(ListRequest $request)
    {
        $board = Workspace\Board\Board::findOrFail($request->get('board_id'));

        $list = $board->layouts()->orderBy('title')->get();

        return LayoutLightResource::collection($list);
    }

    public function getLayout(Workspace\Board\BoardLayout $layout)
    {
        return new LayoutResource($layout);
    }

    public function postCreate(CreateRequest $request)
    {
        $board = Workspace\Board\Board::findOrFail($request->get('board_id'));

        $layout = new Workspace\Board\BoardLayout();
        $layout->title = $request->get('title');
        $layout->layout = $request->get('layout');
        $layout->board_id = $board->id;
        $layout->save();

        return new LayoutResource($layout);
    }

    public function postEdit(Workspace\Board\BoardLayout $layout, UpdateRequest $request)
    {
        $layout->title = $request->get('title');
        $layout->layout = $request->get('layout');
        $layout->save();

        return new LayoutResource($layout);
    }

    public function postDelete(Workspace\Board\BoardLayout $layout)
    {
        $layout->delete();

        return response()->json(['success' => true, 'id' => $layout->id]);
    }

}
