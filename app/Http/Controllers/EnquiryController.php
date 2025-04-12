<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{

    public function index()
    {
        return Inertia::render('pages/enquiries/index');
    }

    public function fetchdata(Request $request)
    {
        $searchQuery = $request->input('searchQuery');
        $perPage = $request->input('perPage');

        $enquiries = Enquiry::query()
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('name', 'like', "%$searchQuery%")
                    ->orWhere('email', 'like', "%$searchQuery%")
                    ->orWhere('phone', 'like', "%$searchQuery%")
                    ->orWhere('message', 'like', "%$searchQuery%")
                    ->orWhere('status', 'like', "%$searchQuery%");
                });
            })
            ->latest()
            ->paginate($perPage, ['id','name', 'email', 'phone', 'message', 'comment', 'status', 'created_at']);

        return response()->json([
            'enquiries' => $enquiries,
        ]);
    }


    public function store(Request $request, $id)
    {
        // Validate incoming data
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'message' => 'nullable|string',
            'comment' => 'nullable|string',
            'status' => 'nullable|string|in:pending,answered,resolved',
        ]);

        $enquiry = Enquiry::create($validatedData);

        // Return success response
        return response()->json([
            'message' => 'Enquiry posted successfully!',
            'data' => $enquiry,
        ], 201);
    }

    /**
     * Update an existing enquiry in the database.
     */
    public function update(Request $request, $id)
    {
        $enquiry = Enquiry::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'message' => 'nullable|string',
            'comment' => 'nullable|string',
            'status' => 'nullable|string|in:pending,answered,resolved',
        ]);

        if ($request->has('status')) {
            $enquiry->status = $request->status;
        }

        $enquiry->update($validatedData);

        return response()->json([
            'message' => 'Enquiry updated successfully!',
            'data' => $enquiry,
        ], 200);
    }


    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return response()->json(['message' => 'Enquiry deleted successfully.']);
    }
}
