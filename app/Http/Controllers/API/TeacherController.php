<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with('user')->get();
        $users = $teachers->pluck('user')->filter();

        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }

    public function store(TeacherRequest $request)
    {
        $role = Role::where('name', 'teacher')->first();
        if (!$role) {
            return response()->json(['message' => 'Teacher role not found'], 422);
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

        $teacher = Teacher::create([
            'user_id'   => $user->id,
            'education' => $request->education,
        ]);

        return response()->json([
            'message' => 'Teacher created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(string $id)
    {
        $teacher = Teacher::with('user')->find($id);
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        return response()->json([
            'data' => new UserResource($teacher->user),
        ]);
    }

    public function update(TeacherRequest $request, string $id)
    {
        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $user = $teacher->user;
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

        $teacher->update([
            'education' => $request->education ?? $teacher->education,
        ]);

        return response()->json([
            'message' => 'Teacher updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(string $id)
    {
        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $teacher->user()->delete(); // soft delete
        $teacher->delete();

        return response()->json(['message' => 'Teacher deleted successfully']);
    }
}
