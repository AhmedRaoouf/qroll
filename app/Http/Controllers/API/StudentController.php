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
        $role = Role::where('name', 'Student')->first();
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
            'password'    => Hash::make($request->national_id),
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

    public function addCourses(Student $student, Request $request)
    {
        if (!$student) {
            return response()->json([
                'message' => 'Student not found',
            ], 404);
        }

        $courseIds = $request->input('course_ids'); // مثلا: [1, 2, 3]

        if (!is_array($courseIds) || empty($courseIds)) {
            return response()->json([
                'message' => 'No course IDs provided',
            ], 400);
        }

        $student->courses()->sync($courseIds);

        return response()->json([
            'message' => 'Courses added successfully',
            'student' => $student->load('courses') // تحميل الكورسات مع الطالب
        ]);
    }

    
}
