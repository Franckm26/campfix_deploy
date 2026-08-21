<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private const DISPLAY_TIMEZONE = 'Asia/Manila';

    private function concernUrlForUser($user, ?int $concernId): string
    {
        $canManageReports = in_array($user->role, ['admin', 'building_admin', 'school_admin', 'academic_head', 'mis']);

        if ($canManageReports) {
            $reportId = $concernId
                ? \App\Models\Report::where('concern_id', $concernId)->latest('id')->value('id')
                : null;

            return '/admin/reports'.($reportId ? '?open_report='.$reportId : '');
        }

        return '/my-concerns'.($concernId ? '?concern_id='.$concernId : '');
    }

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

        // Determine correct URL based on notification type
        $url = null;
        if (str_contains($notification->type, 'Concern')) {
            $concernId = $notification->data['concern_id'] ?? null;
            $url = $this->concernUrlForUser($user, $concernId);
        } elseif (str_contains($notification->type, 'EventRequest')) {
            $eventId = $notification->data['event_id'] ?? null;
            if ($eventId) {
                $url = '/my-events?event_id=' . $eventId;
            } else {
                $url = '/my-events';
            }
        } elseif (str_contains($notification->type, 'Report')) {
            $url = $notification->data['url'] ?? '/reports';
        } else {
            $url = $notification->data['url'] ?? null;
        }

        return response()->json([
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? 'Notification',
            'message' => $notification->data['message'] ?? '',
            'created_at' => $notification->created_at->copy()->timezone(self::DISPLAY_TIMEZONE)->format('M j, Y, g:i A').' PHT',
            'created_at_human' => $notification->created_at->copy()->timezone(self::DISPLAY_TIMEZONE)->diffForHumans(),
            'read_at' => $notification->read_at,
            'url' => $url,
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

            // Concern notifications need a role-aware destination: managers work
            // from Reports, while requesters continue to use My Concerns.
            if ($url && !str_contains($notification->type, 'Concern')) {
                return redirect($url);
            }

            // Fallback: Redirect based on notification type
            if (str_contains($notification->type, 'Concern')) {
                $concernId = $notification->data['concern_id'] ?? null;
                return redirect($this->concernUrlForUser(auth()->user(), $concernId));
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

    public function markAsUnread($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (! $notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->update(['read_at' => null]);

        return response()->json(['success' => true]);
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
