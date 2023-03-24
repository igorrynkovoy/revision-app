<?php

namespace App\Http\Controllers\Api\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\Label\CreateRequest;
use App\Http\Requests\Workspace\Label\ImportCSVRequest;
use App\Http\Requests\Workspace\Label\UpdateRequest;
use App\Http\Resources\Workspaces\LabelResource;
use App\Models\Workspace;
use App\Services\Workspaces\Labels\ImportCSV;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function getList(Workspace $workspace, Request $request)
    {
        $limit = min(100, $request->get('limit', 100));
        $page = max(1, $request->get('page', 1));

        $labels = $workspace->labels()
            ->forPage($page, $limit)
            ->orderBy('key');

        if ($request->filled('blockchain')) {
            $labels->where('blockchain', $request->get('blockchain'));
        }
        if ($request->filled('type')) {
            $labels->where('type', $request->get('type'));
        }
        if ($request->filled('key')) {
            $labels->where('key', 'like', '%'. $request->get('key') . '%');
        }
        if ($request->filled('tag')) {
            $labels->where('tag', $request->get('tag'));
        }

        $labels = $labels->get();

        return LabelResource::collection($labels);
    }

    public function postCreate(Workspace $workspace, CreateRequest $request)
    {
        $exists = Workspace\Label::query()
            ->where('key', $request->get('key'))
            ->where('workspace_id', $workspace->id)
            ->exists();

        if ($exists) {
            abort(403, 'Label already exists');
        }

        $label = new Workspace\Label();
        $label->type = $request->get('type');
        $label->key = $request->get('key');
        $label->label = $request->get('label');
        $label->description = $request->get('description');
        $label->blockchain = $request->get('blockchain');
        $label->tag = $request->get('tag');
        $label->workspace_id = $workspace->id;
        $label->save();

        $resource = new LabelResource($label);

        return $resource;
    }

    public function postImportCSV(Workspace $workspace, ImportCSVRequest $request)
    {
        \Log::info('Import', $request->all());
        $recreateDuplicates = (bool)$request->get('recreate_duplicates', false);

        $importer = new ImportCSV($workspace, $request->file('csv'));
        $importer->replaceDuplicates($recreateDuplicates);
        $importer->save();

        return response()->json([
            'inserted' => $importer->inserted(),
            'updated' => $importer->updated(),
            'errors' => $importer->errors(),
        ]);
    }

    public function postDelete(Workspace $workspace, int $labelId)
    {
        $label = Workspace\Label::query()
            ->where('workspace_id', $workspace->id)
            ->where('id', $labelId)
            ->first();

        if (!$label) {
            abort(404, 'Label not found');
        }

        $label->delete();

        return response()->json(['success' => true, 'id' => $label->id]);
    }

    public function postEdit(Workspace $workspace, int $labelId, UpdateRequest $request)
    {
        /** @var Workspace\Label $label */
        $label = Workspace\Label::query()
            ->where('workspace_id', $workspace->id)
            ->where('id', $labelId)
            ->first();

        if (!$label instanceof Workspace\Label) {
            abort(404, 'Label not found');
        }

        $label->update($request->only(['key', 'label', 'description', 'blockchain', 'tag']));

        $response = new LabelResource($label);

        return response()->json($response);
    }
}
