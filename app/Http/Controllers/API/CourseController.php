<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['doctor.user', 'teacher.user'])->get();

        return response()->json([
            'data' => CourseResource::collection($courses),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'doctor_id'  => 'required|exists:doctors,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $course = Course::create($request->only('name', 'doctor_id', 'teacher_id'));

        return response()->json([
            'message' => 'Course created successfully',
            'data' => new CourseResource($course),
        ], 201);
    }

    public function show(string $id)
    {
        $course = Course::with(['doctor.user', 'teacher.user'])->find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json(['data' => $course]);
    }

    public function update(Request $request, string $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $request->validate([
            'name'       => 'sometimes|string|max:255',
            'doctor_id'  => 'sometimes|exists:doctors,id',
            'teacher_id' => 'sometimes|exists:teachers,id',
        ]);

        $course->update($request->only('name', 'doctor_id', 'teacher_id'));

        return response()->json([
            'message' => 'Course updated successfully',
            'data' => new CourseResource($course),
        ]);
    }

    public function destroy(string $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }
}
