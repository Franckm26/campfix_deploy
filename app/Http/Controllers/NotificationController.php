<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
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
                    return redirect()->route('concerns.show', $concernId);
                }
                return redirect()->route('concerns.my');
            } elseif (str_contains($notification->type, 'ConcernAssigned')) {
                return redirect()->route('concerns.my');
            } elseif (str_contains($notification->type, 'ConcernResolved')) {
                $concernId = $notification->data['concern_id'] ?? null;
                if ($concernId) {
                    return redirect()->route('concerns.show', $concernId);
                }
                return redirect()->route('concerns.my');
            } elseif (str_contains($notification->type, 'ReportAssigned') || str_contains($notification->type, 'ReportResolved')) {
                $reportId = $notification->data['report_id'] ?? null;
                if ($reportId) {
                    return redirect()->route('reports.show', $reportId);
                }
                return redirect()->route('reports.index');
            } elseif (str_contains($notification->type, 'EventRequest')) {
                return redirect()->route('events.my');
            } elseif (str_contains($notification->type, 'NewEventRequest')) {
                return redirect()->route('admin.events');
            } else {
                return redirect('/dashboard');
            }
        }

        return redirect('/dashboard');
    }

    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->delete();
        }

        return back();
    }
}
