<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::with('user')->get();
        $users = $admins->pluck('user')->filter();
        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }

    public function store(AdminRequest $request)
    {
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'national_id' => $request->national_id,
            'birth_date'  => $request->birth_date,
            'address'     => $request->address,
            'password'    => Hash::make($request->password),
            'role_id'     => Role::where('name', 'Admin')->first()->id,
        ]);

        $admin = Admin::create([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Admin created successfully',
            'data' => $admin->load('user'),
        ], 201);
    }

    public function show(string $id)
    {
        $admin = Admin::with('user')->find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }
        return response()->json([
            'data' => new UserResource($admin->user),
        ]);
    }

    public function update(AdminRequest $request, string $id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }
        $user = $admin->user;
        $user->update([
            'name'        => $request->name ?? $user->name,
            'email'       => $request->email ?? $user->email,
            'phone'       => $request->phone ?? $user->phone,
            'national_id' => $request->national_id ?? $user->national_id,
            'birth_date'  => $request->birth_date ?? $user->birth_date,
            'address'     => $request->address ?? $user->address,
        ]);

        return response()->json([
            'message' => 'Admin updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(string $id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }
        $admin->user()->delete(); // soft delete
        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully']);
    }
}
