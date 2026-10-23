<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\Report;
use App\Services\AssetReplacementService;
use Illuminate\Http\Request;

class AssetReplacementController extends Controller
{
    public function store(Request $request, string $resource, int $id)
    {
        abort_unless(in_array($resource, ['concerns', 'reports'], true), 404);
        $data = $request->validate([
            'replacement_details' => 'required|string|max:255',
            'replacement_notes' => 'required|string|max:1000',
            'operation_id' => 'required|uuid',
            'confirmed_replacement' => 'accepted',
        ]);
        $ticket = $resource === 'concerns' ? Concern::findOrFail($id) : Report::findOrFail($id);
        $replacement = app(AssetReplacementService::class)->record($ticket, $request->user(),
            trim($data['replacement_details']), trim($data['replacement_notes']), $data['operation_id']);
        return response()->json(['success' => true, 'replacement_id' => $replacement->id,
            'message' => 'Asset replacement recorded. The previous asset history is preserved and the new asset starts a fresh recurrence count.']);
    }
}
