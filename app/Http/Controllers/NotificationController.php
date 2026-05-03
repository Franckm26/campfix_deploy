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

            // Redirect based on notification type

            if (str_contains($notification->type, 'Concern')) {

                return redirect()->route('concerns.assigned');

            } elseif (str_contains($notification->type, 'EventRequest')) {

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
