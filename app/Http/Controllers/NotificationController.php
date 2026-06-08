<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notification detail with adjacent notification IDs for navigation
     */
    public function show($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        // Get all notification IDs in chronological order
        $allNotificationIds = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->pluck('id')
            ->toArray();

        $currentIndex = array_search($id, $allNotificationIds);
        
        // Get previous and next notification IDs
        $prevId = $currentIndex > 0 ? $allNotificationIds[$currentIndex - 1] : null;
        $nextId = $currentIndex < count($allNotificationIds) - 1 ? $allNotificationIds[$currentIndex + 1] : null;

        // Get sender information if available
        $sender = null;
        if (isset($notification->data['sender_id'])) {
            $sender = \App\Models\User::find($notification->data['sender_id']);
        }

        return response()->json([
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? 'Notification',
            'message' => $notification->data['message'] ?? '',
            'created_at' => $notification->created_at->format('M j, g:i a'),
            'created_at_human' => $notification->created_at->diffForHumans(),
            'read_at' => $notification->read_at,
            'url' => $notification->data['url'] ?? null,
            'sender' => $sender ? [
                'name' => $sender->name,
                'profile_picture' => $sender->profile_picture ? asset('storage/' . $sender->profile_picture) : null,
            ] : null,
            'prev_id' => $prevId,
            'next_id' => $nextId,
        ]);
    }

    public function read($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            // Mark as read
            $notification->markAsRead();

            // Get the URL from notification data if available
            $url = $notification->data['url'] ?? null;

            // If notification has a specific URL, redirect there
            if ($url) {
                return redirect($url);
            }

            // Fallback: Redirect based on notification type
            if (str_contains($notification->type, 'ConcernFollowUp')) {
                // For follow-up notifications, redirect to the specific concern
                $concernId = $notification->data['concern_id'] ?? null;
                if ($concernId) {
                    return redirect('/my-concerns?concern_id=' . $concernId);
                }
                return redirect('/my-concerns');
            } elseif (str_contains($notification->type, 'ConcernAssigned')) {
                $concernId = $notification->data['concern_id'] ?? null;
                if ($concernId) {
                    return redirect('/my-concerns?concern_id=' . $concernId);
                }
                return redirect('/my-concerns');
            } elseif (str_contains($notification->type, 'ConcernResolved')) {
                $concernId = $notification->data['concern_id'] ?? null;
                if ($concernId) {
                    return redirect('/my-concerns?concern_id=' . $concernId);
                }
                return redirect('/my-concerns');
            } elseif (str_contains($notification->type, 'ReportAssigned') || str_contains($notification->type, 'ReportResolved')) {
                $reportId = $notification->data['report_id'] ?? null;
                if ($reportId) {
                    return redirect()->route('reports.show', $reportId);
                }
                return redirect()->route('reports.index');
            } elseif (str_contains($notification->type, 'EventRequest')) {
                $eventId = $notification->data['event_id'] ?? null;
                if ($eventId) {
                    return redirect('/my-events?event_id=' . $eventId);
                }
                return redirect('/my-events');
            } elseif (str_contains($notification->type, 'NewEventRequest')) {
                return redirect()->route('admin.events');
            } else {
                return redirect('/dashboard');
            }
        }

        return redirect('/dashboard');
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }

    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }
}
