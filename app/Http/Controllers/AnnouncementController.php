<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the announcements.
     */
    public function index()
    {
        return Inertia::render('pages/Announcements/Index');
    }


    public function fetchData(Request $request)
    {
        $query = Announcement::query();

        if ($request->searchQuery) {
            $query->where('title', 'like', '%' . $request->searchQuery . '%')
            ->orWhere('description', 'like', '%' . $request->searchQuery . '%')
            ->orWhere('type', 'like', '%' . $request->searchQuery . '%')
            ->orWhere('date', 'like', '%' . $request->searchQuery . '%');
        }

        $announcements = $query->orderBy('date', 'desc')
            ->paginate($request->perPage ?? 10);

        return response()->json([
            'announcements' => $announcements
        ]);
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $announcement = Announcement::create($validated);

        return response()->json($announcement, 201);
    }

    /**
     * Update the specified announcement in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $announcement->update($validated);

        return response()->json($announcement);
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully.']);
    }
}
