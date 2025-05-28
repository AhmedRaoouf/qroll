<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\UserResource;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\User;
use App\Models\Role;
use App\Models\Section;
use App\Models\StudentCourse;
use App\Models\StudentLecture;
use App\Models\StudentSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $role = Role::where('name', 'student')->first();
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
            'password'    => Hash::make($request->password),
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

    public function addCourses(string $id, Request $request)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $request->validate([
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        $courseIds = $request->input('course_ids');

        // Sync courses: remove old, attach new
        $student->courses()->sync($courseIds);

        $lectureIds = Lecture::whereIn('course_id', $courseIds)->pluck('id')->toArray();

        // Sync lectures to student
        $student->lectures()->sync($lectureIds);
        return response()->json([
            'message' => 'Courses synced successfully',
            'student' => $student->load('courses'),
        ]);
    }


    // لإرجاع الكورسات المرتبطة بالطالب
    public function courses(string $id)
    {
        $student = Student::with('courses')->find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'message' => 'Student courses retrieved successfully',
            'student' => new UserResource($student->user),
            'courses' => $student->courses,
        ]);
    }

    public function scanQR(Request $request)
{
    $data = json_decode($request->input('data'), true);

    // تحقق من صحة البيانات المرسلة
    if (!$data || !isset($data['timestamp'], $data['signature'])) {
        return response()->json(['message' => 'Invalid QR data'], 422);
    }

    // تحديد ما إذا كان QR يخص محاضرة أم سكشن
    if (isset($data['lecture_id'])) {
        $id = $data['lecture_id'];
        $type = 'lecture';
    } elseif (isset($data['section_id'])) {
        $id = $data['section_id'];
        $type = 'section';
    } else {
        return response()->json(['message' => 'Invalid QR type'], 422);
    }

    // تحقق من صحة التوقيع
    $validSignature = hash_hmac('sha256', $id . $data['timestamp'], config('app.key'));
    if ($validSignature !== $data['signature']) {
        return response()->json(['message' => 'QR code tampered'], 403);
    }

    // تحقق من الوقت (اختياري - غير مفعل حالياً)
    // if (now()->timestamp - $data['timestamp'] > 600) {
    //     return response()->json(['message' => 'QR code expired'], 410);
    // }

    $student = Auth::guard('api')->user()->student;

    if ($type === 'lecture') {
        $lecture = Lecture::find($id);
        if (!$lecture) {
            return response()->json(['message' => 'Lecture not found'], 404);
        }

        // تحقق من تسجيل الطالب في الكورس
        if (!$student->courses()->where('courses.id', $lecture->course_id)->exists()) {
            return response()->json(['message' => 'Not enrolled'], 403);
        }

        // تسجيل الحضور
        StudentLecture::updateOrCreate([
            'student_id' => $student->id,
            'lecture_id' => $lecture->id
        ], [
            'status' => 'true',
            'updated_at' => now(),
        ]);
    } else {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        // تحقق من تسجيل الطالب في الكورس
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
    }

    return response()->json(['message' => 'Attendance recorded']);
}


}
