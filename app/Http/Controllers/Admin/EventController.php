<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('date')->get();

        return view('admin.events', compact('events'));
    }

    public function store(Request $request)
    {
        Event::create($request->all());

        return back()->with('success', 'Event added');
    }
}
