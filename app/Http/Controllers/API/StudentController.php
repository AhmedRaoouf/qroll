<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\UserResource;
use App\Models\Student;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user')->get();
        $users = $students->pluck('user')->filter();

        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }

    public function store(StudentRequest $request)
    {
        $role = Role::where('name', 'student')->first();
        if (!$role) {
            return response()->json(['message' => 'Student role not found'], 422);
        }

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'national_id' => $request->national_id,
            'birth_date'  => $request->birth_date,
            'address'     => $request->address,
            'password'    => Hash::make($request->password),
            'role_id'     => $role->id,
        ]);

        $student = Student::create([
            'user_id'     => $user->id,
            'academic_id' => $request->academic_id,
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(string $id)
    {
        $student = Student::with('user')->find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'data' => new UserResource($student->user),
        ]);
    }

    public function update(StudentRequest $request, string $id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $user = $student->user;
        $user->update([
            'name'        => $request->name ?? $user->name,
            'email'       => $request->email ?? $user->email,
            'phone'       => $request->phone ?? $user->phone,
            'national_id' => $request->national_id ?? $user->national_id,
            'birth_date'  => $request->birth_date ?? $user->birth_date,
            'address'     => $request->address ?? $user->address,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        $student->update([
            'academic_id' => $request->academic_id ?? $student->academic_id,
        ]);

        return response()->json([
            'message' => 'Student updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(string $id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->user()->delete(); // soft delete
        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    public function addCourses(string $id, Request $request)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $request->validate([
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        $courseIds = $request->input('course_ids');

        // Check if the student is already enrolled in any of the courses
        $existingCourses = $student->courses()->whereIn('courses.id', $courseIds)->pluck('courses.id')->toArray();
        if (!empty($existingCourses)) {
            return response()->json([
                'message' => 'Student is already enrolled in courses: ' . implode(', ', $existingCourses),
            ], 400);
        }

        // Sync the courses (attach new courses, keep existing ones)
        $student->courses()->sync($courseIds);

        return response()->json([
            'message' => 'Courses added successfully',
            'student' => $student->load('courses'), // Load courses with the student
        ]);
    }

    // لإرجاع الكورسات المرتبطة بالطالب
    public function courses($studentId)
    {
        $student = Student::with('courses')->find($studentId);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'message' => 'Student courses retrieved successfully',
            'student' => new UserResource($student->user),
            'courses' => $student->courses,
        ]);
    }

    // لإزالة كورسات معينة من الطالب
    public function removeCourses($studentId, Request $request)
    {
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $request->validate([
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        $student->courses()->detach($request->course_ids);

        return response()->json([
            'message' => 'Courses removed successfully',
            'remaining_courses' => $student->courses, // Optional
        ]);
    }
}
