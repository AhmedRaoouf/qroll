<?php

namespace App\Http\Controllers\API;

use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::guard('api')->user();
        return response()->json([
            'data' => new UserResource($user)
        ]);
    }

    public function update(ProfileRequest $request)
    {
        $data = $request->validated();
        $user = Auth::guard('api')->user();
        if ($request->image) {
            if ($user->image && file_exists($user->image)) {
                unlink(public_path($user->image));
            }
            $image = $this->uploadImage($request->image);
            $data['image'] = $image;
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'birth_date' => $data['birth_date'],
            'national_id' => $data['national_id'],
            'image' => $data['image'],
        ]);
        return response()->json([
            'data' => new UserResource($user)
        ]);
    }

    public static function uploadImage($image)
    {
        $imageConverter = Image::make($image)->encode('webp', 90);
        $newFileName = rand(10000, 99999) . time() . '.webp';
        $destinationPath = 'storage/files';
        $imageConverter->save($destinationPath . '/' . $newFileName);
        return '/storage/files/' . $newFileName;
    }
}
