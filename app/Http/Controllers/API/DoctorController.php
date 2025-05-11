<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('user')->get();
        $users = $doctors->pluck('user')->filter();

        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }

    public function store(TeacherRequest $request)
    {
        $role = Role::where('name', 'Doctor')->first();
        if (!$role) {
            return response()->json(['message' => 'Doctor role not found'], 422);
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

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'education' => $request->education,
        ]);

        return response()->json([
            'message' => 'Doctor created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(string $id)
    {
        $doctor = Doctor::with('user')->find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json([
            'data' => new UserResource($doctor->user),
        ]);
    }

    public function update(TeacherRequest $request, string $id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $user = $doctor->user;
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

        $doctor->update([
            'education' => $request->education ?? $doctor->education,
        ]);

        return response()->json([
            'message' => 'Doctor updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(string $id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $doctor->user()->delete(); // soft delete
        $doctor->delete();

        return response()->json(['message' => 'Doctor deleted successfully']);
    }
}
