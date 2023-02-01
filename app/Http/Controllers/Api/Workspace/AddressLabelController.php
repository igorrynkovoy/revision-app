<?php

namespace App\Http\Controllers\Api\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\AddressLabel\CreateRequest;
use App\Http\Requests\Workspace\AddressLabel\UpdateRequest;
use App\Http\Resources\Workspaces\AddressLabelResource;
use App\Http\Resources\Workspaces\WorkspaceResource;
use App\Models\Workspace;
use App\Services\Workspaces\AddressLabels\ImportCSV;
use Illuminate\Http\Request;

class AddressLabelController extends Controller
{
    public function getList(Workspace $workspace, Request $request)
    {
        $limit = min(100, $request->get('limit', 100));
        $page = max(1, $request->get('page', 1));

        $labels = $workspace->addressLabels()
            ->forPage($page, $limit)
            ->orderBy('address');

        if ($request->filled('blockchain')) {
            $labels->where('blockchain', $request->get('blockchain'));
        }
        if ($request->filled('address')) {
            $labels->where('address', $request->get('address'));
        }
        if ($request->filled('tag')) {
            $labels->where('tag', $request->get('tag'));
        }

        $labels = $labels->get();

        return AddressLabelResource::collection($labels);
    }

    public function postCreate(Workspace $workspace, CreateRequest $request)
    {
        $exists = Workspace\AddressLabel::query()
            ->where('address', $request->get('address'))
            ->where('workspace_id', $workspace->id)
            ->exists();

        if ($exists) {
            abort(403, 'Address label already exists');
        }

        $addressLabel = new Workspace\AddressLabel();
        $addressLabel->address = $request->get('address');
        $addressLabel->label = $request->get('label');
        $addressLabel->description = $request->get('description');
        $addressLabel->blockchain = $request->get('blockchain');
        $addressLabel->tag = $request->get('tag');
        $addressLabel->workspace_id = $workspace->id;
        $addressLabel->save();

        $resource = new AddressLabelResource($addressLabel);

        return $resource;
    }

    public function postImportCSV(Workspace $workspace, Request $request)
    {
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

    public function postDelete(Workspace $workspace, int $addressLabelID)
    {
        $label = Workspace\AddressLabel::query()
            ->where('workspace_id', $workspace->id)
            ->where('id', $addressLabelID)
            ->first();

        if (!$label) {
            abort(404, 'Label not found');
        }

        $label->delete();

        return response()->json(['success' => true, 'id' => $label->id]);
    }

    public function postEdit(Workspace $workspace, int $addressLabelID, UpdateRequest $request)
    {
        /** @var Workspace\AddressLabel $label */
        $addressLabel = Workspace\AddressLabel::query()
            ->where('workspace_id', $workspace->id)
            ->where('id', $addressLabelID)
            ->first();

        if (!$addressLabel instanceof Workspace\AddressLabel) {
            abort(404, 'Label not found');
        }

        $addressLabel->update($request->only(['address', 'label', 'description', 'blockchain', 'tag']));

        $response = new AddressLabelResource($addressLabel);

        return response()->json($response);
    }
}
