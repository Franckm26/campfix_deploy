<?php

namespace App\Http\Controllers;

use App\Models\EventDiscussion;
use App\Models\EventRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventDiscussionController extends Controller
{
    /**
     * Get all discussions for an event request
     */
    public function index(EventRequest $eventRequest)
    {
        $discussions = $eventRequest->discussions()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($discussions);
    }

    /**
     * Store a new discussion message
     */
    public function store(Request $request, EventRequest $eventRequest)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $discussion = EventDiscussion::create([
            'event_request_id' => $eventRequest->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $discussion->load('user');

        return response()->json($discussion, 201);
    }

    /**
     * Delete a discussion message (only by the author)
     */
    public function destroy(EventDiscussion $discussion)
    {
        if ($discussion->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $discussion->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }
}
