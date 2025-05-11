<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SectionResource;
use Illuminate\Http\Request;
use App\Models\Section;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::with('course')->get();

        return response()->json([
            'data' => SectionResource::collection($sections),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'qr_code'   => 'nullable|string|max:255',
        ]);

        $section = Section::create($request->only('name', 'course_id', 'qr_code'));

        return response()->json([
            'message' => 'Section created successfully',
            'data'    => new SectionResource($section),
        ], 201);
    }

    public function show(string $id)
    {
        $section = Section::with('course')->find($id);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        return response()->json([
            'data' => new SectionResource($section),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'course_id' => 'sometimes|exists:courses,id',
            'qr_code'   => 'nullable|string|max:255',
        ]);

        $section->update($request->only('name', 'course_id', 'qr_code'));

        return response()->json([
            'message' => 'Section updated successfully',
            'data'    => new SectionResource($section),
        ]);
    }

    public function destroy(string $id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $section->delete();

        return response()->json(['message' => 'Section deleted successfully']);
    }
}
