<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SectionResource;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentSection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::with('course')->get();

        return response()->json([
            'data' => SectionResource::collection($sections),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'qr_code'   => 'nullable|string|max:255',
        ]);

        $section = Section::create($request->only('name', 'course_id', 'qr_code'));

        return response()->json([
            'message' => 'Section created successfully',
            'data'    => new SectionResource($section),
        ], 201);
    }

    public function show(string $id)
    {
        $section = Section::with('course')->find($id);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        return response()->json([
            'data' => new SectionResource($section),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'course_id' => 'sometimes|exists:courses,id',
            'qr_code'   => 'nullable|string|max:255',
        ]);

        $section->update($request->only('name', 'course_id', 'qr_code'));

        return response()->json([
            'message' => 'Section updated successfully',
            'data'    => new SectionResource($section),
        ]);
    }

    public function destroy(string $id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $section->delete();

        return response()->json(['message' => 'Section deleted successfully']);
    }


    public function generateQR(string $id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }
        $payload = [
            'section_id' => $section->id,
            'course_id' => $section->course_id,
            'timestamp' => now()->timestamp,
            'signature' => hash_hmac('sha256', $section->id . now()->timestamp, config('app.key'))
        ];

        $qrContent = json_encode($payload);
        $section->update(['qr_code' => $qrContent]);
        return response()->json([
            'qr' => base64_encode(QrCode::format('png')->size(200)->generate($qrContent))
        ]);
    }

    public function scanQR(Request $request)
    {
        $data = json_decode($request->input('data'), true);

        if (!$data || !isset($data['section_id'], $data['timestamp'], $data['signature'])) {
            return response()->json(['message' => 'Invalid QR data'], 422);
        }

        $validSignature = hash_hmac('sha256', $data['section_id'] . $data['timestamp'], config('app.key'));
        if ($validSignature !== $data['signature']) {
            return response()->json(['message' => 'QR code tampered'], 403);
        }

        // if (now()->timestamp - $data['timestamp'] > 600) {
        //     return response()->json(['message' => 'QR code expired'], 410);
        // }

        $section = Section::find($data['section_id']);
        if (!$section) {
            return response()->json(['message' => 'section not found'], 404);
        }

        $student = Auth::guard('api')->user();

        // تحقق إن الطالب مسجل في الكورس
        if (!$student->courses()->where('courses.id', $section->course_id)->exists()) {
            return response()->json(['message' => 'Not enrolled'], 403);
        }

        // تسجيل الحضور
        StudentSection::updateOrCreate([
            'student_id' => $student->id,
            'section_id' => $section->id
        ], [
            'status' => 'true',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Attendance recorded']);
    }
}
