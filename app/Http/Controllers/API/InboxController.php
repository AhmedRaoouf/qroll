<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MassageResource;
use App\Models\Inbox;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InboxController extends Controller
{
    // 👨‍🎓 Student: Get all received messages
    public function index()
    {
        $student = Student::where('user_id', Auth::guard('api')->user()->id)->first();
        $messages = Inbox::where('receiver_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return MassageResource::collection($messages);
    }

    // 👨‍🎓 Student: View single message
    public function show($id)
    {
        $student = Student::where('user_id', Auth::guard('api')->user()->id)->first();
        $message = Inbox::where('id', $id)
            ->where('receiver_id', $student->id)
            ->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        return new MassageResource($message);
    }

    // 👨‍🏫 Admin/Doctor: Send message
    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:students,id',
            'message'     => 'required|string',
        ]);

        $inbox = Inbox::create([
            'sender_id'   => Auth::guard('api')->user()->id,
            'receiver_id' => $data['receiver_id'],
            'message'     => $data['message'],
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => new MassageResource($inbox),
        ], 201);
    }

    public function takeAction(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:lecture,section',
            'absence_percentage' => 'required|numeric|min:0|max:100',
            'course_id' => 'required|exists:courses,id',
            'message' => 'required|string',
        ]);

        $courseId = $data['course_id'];
        $percentageLimit = $data['absence_percentage'];
        $type = $data['type'];
        $messageText = $data['message'];
        $senderId = Auth::guard('api')->user()->id;

        // هات كل الطلاب المرتبطين بالكورس المطلوب
        $studentIds = DB::table('student_courses')
            ->where('course_id', $courseId)
            ->pluck('student_id');

        foreach ($studentIds as $studentId) {
            if ($type === 'lecture') {
                $query = DB::table('student_lectures')
                    ->join('lectures', 'student_lectures.lecture_id', '=', 'lectures.id')
                    ->where('student_lectures.student_id', $studentId)
                    ->where('lectures.course_id', $courseId);
            } else {
                $query = DB::table('student_sections')
                    ->join('sections', 'student_sections.section_id', '=', 'sections.id')
                    ->where('student_sections.student_id', $studentId)
                    ->where('sections.course_id', $courseId);
            }

            $total = (clone $query)->count();
            $absent = (clone $query)->where($type === 'lecture' ? 'student_lectures.status' : 'student_sections.status', 'false')->count();

            if ($total === 0) {
                continue;
            }

            $absencePercentage = ($absent / $total) * 100;

            if ($absencePercentage >= $percentageLimit) {
                \App\Models\Inbox::create([
                    'sender_id' => $senderId,
                    'receiver_id' => $studentId,
                    'message' => $messageText,
                ]);
            }
        }

        return response()->json([
            'message' => 'Messages sent successfully to students with high absence.',
        ]);
    }
}
