<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * Show a read-only history of actions performed by the signed-in account.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_if(
            $user->role === 'mis' || $user->role === 'superadmin' || $user->is_superadmin,
            403,
            'MIS accounts must use the Audit Logs module.'
        );

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50,100'],
        ]);

        $query = ActivityLog::query()
            ->where('user_id', $user->id)
            ->where('action', '!=', 'event_auto_rejected_expired');

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);
            $query->where(function ($builder) use ($search) {
                $builder->where('action', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (! empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $history = $query->latest('created_at')
            ->paginate((int) ($validated['per_page'] ?? 20))
            ->withQueryString();

        return view('history.index', compact('history'));
    }
}
