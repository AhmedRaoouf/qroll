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


    public function generateQR(Lecture $lecture)
    {
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

    public function scanQR(Request $request)
    {
        $data = json_decode($request->input('data'), true);

        if (!$data || !isset($data['lecture_id'], $data['timestamp'], $data['signature'])) {
            return response()->json(['message' => 'Invalid QR data'], 422);
        }

        $validSignature = hash_hmac('sha256', $data['lecture_id'] . $data['timestamp'], config('app.key'));
        if ($validSignature !== $data['signature']) {
            return response()->json(['message' => 'QR code tampered'], 403);
        }

        // if (now()->timestamp - $data['timestamp'] > 600) {
        //     return response()->json(['message' => 'QR code expired'], 410);
        // }

        $lecture = Lecture::find($data['lecture_id']);
        if (!$lecture) {
            return response()->json(['message' => 'Lecture not found'], 404);
        }

        $student = Auth::user();

        // تحقق إن الطالب مسجل في الكورس
        if (!$student->courses()->where('courses.id', $lecture->course_id)->exists()) {
            return response()->json(['message' => 'Not enrolled'], 403);
        }

        // تسجيل الحضور
        StudentLecture::updateOrCreate([
            'student_id' => $student->id,
            'lecture_id' => $lecture->id
        ], [
            'status' => 'true'
        ]);

        return response()->json(['message' => 'Attendance recorded']);
    }
}
