<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

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

    public function sections(string $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found',
            ]);
        }
        $sections = $course->sections;
        return response()->json(['data' => $sections]);
    }

    public function lectures(string $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found',
            ]);
        }
        $lectures = $course->lectures;
        return response()->json(['data' => $lectures]);
    }

    public function allStudents($courseId)
    {
        $course = Course::with('students')->find($courseId);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found',
            ], 404);
        }

        return response()->json([
            'course' => $course->name,
            'students' => $course->students,
        ]);
    }

    public function addStudentToCourse(string $id, Request $request)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found',
            ]);
        }
        $request->validate([
            'academic_id' => 'required',
        ]);

        $student = Student::where('academic_id', $request->academic_id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        if ($course->students()->where('student_id', $student->id)->exists()) {
            return response()->json(['message' => 'Student already enrolled in this course'], 400);
        }

        $course->students()->attach($student->id);
        foreach ($course->lectures as $lecture) {
            if (!$lecture->students()->where('student_id', $student->id)->exists()) {
                $lecture->students()->attach($student->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }


        return response()->json(['message' => 'Student added to course and lectures successfully']);
    }

    public function addStudentToSections(string $id, Request $request)
    {
        $request->validate([
            'academic_id' => 'required',
        ]);

        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $student = Student::where('academic_id', $request->academic_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        // تأكد إنه بالفعل مضاف للكورس
        if (!$course->students()->where('student_id', $student->id)->exists()) {
            return response()->json(['message' => 'Student is not enrolled in this course'], 400);
        }

        // ضيفه لكل السكاشن المرتبطة بالكورس
        foreach ($course->sections as $section) {
            if (!$section->students()->where('student_id', $student->id)->exists()) {
                $section->students()->attach($student->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Student added to all sections in the course successfully']);
    }


    public function myCourses()
    {
        $user = Auth::guard('api')->user();

        if ($user->hasRole('student') && $user->student) {
            return $user->student->courses;
        }

        if ($user->hasRole('doctor') && $user->doctor) {
            return $user->doctor->courses;
        }

        if ($user->hasRole('teacher') && $user->teacher) {
            return $user->teacher->courses;
        }

        return response()->json(['message' => 'No courses found for this user.'], 404);
    }
}
