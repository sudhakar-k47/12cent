<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GalleryController extends Controller
{
    /**
     * Display a listing of the gallery items.
     */
    public function index(Request $request)
    {
        $query = Gallery::query();

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $galleries = $query->latest()->paginate(10);

        // Decode images JSON to array
        $galleries->getCollection()->transform(function ($gallery) {
            $gallery->images = json_decode($gallery->images ?? '[]', true);
            return $gallery;
        });

        return Inertia::render('pages/Gallery/Index', [
            'galleries' => $galleries,
            'filters' => $request->only('search'),
        ]);
    }
    /**
     * Store a newly created gallery item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'required|array',
            'images.*' => 'image|max:5120', // 5MB max per image
            'type' => 'required|string|in:Meeting,Event',
            'is_active' => 'required|boolean',
        ]);

        // Initialize the images array
        $imagesPaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('galleries/images', $filename, 'public');

                if ($path) {
                    $imagePaths[] = Storage::url($path);
                }
            }
        }


        // Create the gallery with JSON images
        $gallery = Gallery::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'is_active' => $validated['is_active'],
            'images' => json_encode($imagePaths),
        ]);

        return response()->json($gallery, 201);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120', // 5MB max per image
            'type' => 'required|string|in:Meeting,Event',
            'is_active' => 'required|boolean',
            'existing_images' => 'nullable|array',
        ]);

        // Get existing images from the request
        $existingImages = $validated['existing_images'] ?? [];

        // Get current images from the database
        $currentImages = json_decode($gallery->images ?? '[]', true) ?? [];

        // Find images to delete (images that were in currentImages but not in existingImages)
        $imagesToDelete = array_diff($currentImages, $existingImages);

        // Delete removed images from storage
        foreach ($imagesToDelete as $imagePath) {
            try {
                $path = str_replace('/storage/', '', $imagePath);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to delete image: ' . $e->getMessage());
            }
        }

        $newImagePaths = $existingImages; // Start with existing images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                try {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('galleries/images', $filename, 'public');

                    if ($path) {
                        $newImagePaths[] = Storage::url($path);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to upload image: ' . $e->getMessage());
                    continue;
                }
            }
        }

        // Update the gallery with new data
        $gallery->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'is_active' => $validated['is_active'],
            'images' => json_encode($newImagePaths),
        ]);

        return response()->json($gallery, 200);
    }


    public function destroy(Gallery $gallery)
    {
        // Delete all images from storage
        $images = json_decode($gallery->images ?? '[]', true);
        foreach ($images as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        // Delete the gallery record
        $gallery->delete();

        return response()->json(null, 204);
    }
}
