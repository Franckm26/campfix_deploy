<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\Report;
use App\Services\ReopenTicketService;
use Illuminate\Http\Request;

class ReopenTicketController extends Controller
{
    public function concern(Request $request, Concern $concern, ReopenTicketService $service)
    {
        return $this->reopen($request, $concern, $service);
    }

    public function report(Request $request, Report $report, ReopenTicketService $service)
    {
        return $this->reopen($request, $report, $service);
    }

    private function reopen(Request $request, Concern|Report $ticket, ReopenTicketService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $ticket = $service->reopen($ticket, $request->user(), trim($data['reason']));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Ticket reopened successfully.', 'status' => $ticket->status]);
        }

        return redirect()->back()->with('success', 'Ticket reopened successfully. It is active again.');
    }
}
