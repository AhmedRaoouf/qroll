<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        //Admin
        if ($data['role_id'] == 1) {
            $user = User::where('email', $request->email)
                ->where('role_id', $request->role_id)
                ->first();
        } else {
            //Other Roles
            $user = User::where('national_id', $request->email)
                ->where('role_id', $request->role_id)
                ->first();
        }


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('qroll-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function ChangePassword(Request $request)
    {
        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['message' => 'Password changed successfully']);
    }

    public function resetPassword(Request $request)
    {
        $user = auth()->user();
        if (!$user || !Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Old password not correct!'], 401);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['message' => 'Password changed successfully']);
    }
}
