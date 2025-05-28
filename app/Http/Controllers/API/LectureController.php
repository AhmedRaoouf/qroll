<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LectureResource;
use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Models\StudentLecture;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LectureController extends Controller
{
    public function index()
    {
        $lectures = Lecture::with('course')->get();

        return response()->json([
            'data' => LectureResource::collection($lectures),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'qr_code'   => 'nullable|string|max:255',
        ]);

        $lecture = Lecture::create($request->only('name', 'course_id', 'qr_code'));

        return response()->json([
            'message' => 'Lecture created successfully',
            'data'    => new LectureResource($lecture),
        ], 201);
    }

    public function show(string $id)
    {
        $lecture = Lecture::with('course')->find($id);

        if (!$lecture) {
            return response()->json(['message' => 'Lecture not found'], 404);
        }

        return response()->json([
            'data' => new LectureResource($lecture),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json(['message' => 'Lecture not found'], 404);
        }

        $request->validate([
            'name'      => 'sometimes|string|max:255',
        ]);

        $lecture->update($request->only('name'));

        return response()->json([
            'message' => 'Lecture updated successfully',
            'data'    => new LectureResource($lecture),
        ]);
    }

    public function destroy(string $id)
    {
        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json(['message' => 'Lecture not found'], 404);
        }

        $lecture->delete();

        return response()->json(['message' => 'Lecture deleted successfully']);
    }


    public function generateQR(string $id)
    {
        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json(['message' => 'Lecture not found'], 404);
        }
        $payload = [
            'lecture_id' => $lecture->id,
            'course_id' => $lecture->course_id,
            'timestamp' => now()->timestamp,
            'signature' => hash_hmac('sha256', $lecture->id . now()->timestamp, config('app.key'))
        ];

        $qrContent = json_encode($payload);
        $lecture->update(['qr_code' => $qrContent]);
        return response()->json([
            'qr' => base64_encode(QrCode::format('png')->size(200)->generate($qrContent))
        ]);
    }

   
}
