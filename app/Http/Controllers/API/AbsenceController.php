<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Absence;
use App\Models\Course;
use App\Models\Lecture;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    public function getAbsencesByCourse(Course $course)
    {
        $students = Student::whereHas('lectures.course', function ($q) use ($course) {
            $q->where('id', $course->id);
        })->get();

        $lectures = Lecture::where('course_id', $course->id)->pluck('id');

        $data = $students->map(function ($student) use ($lectures) {
            $absentLectures = Absence::where('student_id', $student->id)
                ->whereIn('lecture_id', $lectures)
                ->pluck('lecture_id');

            $totalLectures = $lectures->count();
            $absentCount = $absentLectures->count();
            $absencePercentage = $totalLectures > 0
                ? round(($absentCount / $totalLectures) * 100)
                : 0;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'academic_id' => $student->academic_id,
                'number_of_absence' => $absentCount,
                'lecture_numbers' => $absentLectures->implode(','),
                'absence_percentage' => "{$absencePercentage}%",
            ];
        });

        return response()->json($data);
    }


    public function getAbsencesByLecture(Course $course, Lecture $lecture)
    {

        $students = Student::whereHas('lectures', function ($q) use ($lecture, $course) {
            $q->where('lectures.id', $lecture->id)
                ->where('lectures.course_id', $course->id);
        })->get();

        $data = $students->map(function ($student) use ($lecture) {
            $absence = Absence::where('student_id', $student->id)
                ->where('lecture_id', $lecture->id)
                ->first();

            $status = 'Absent';

            if ($absence && $absence->status === true) {
                $status = 'Present';
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'academic_id' => $student->academic_id,
                'status' => $status,
            ];
        });

        return response()->json($data);
    }

    public function getStudentLectureAbsences(Course $course)
    {
        $student = Auth::guard('api')->user()->student;

        if (!$student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        // Get all lectures related to this course
        $lectures = Lecture::where('course_id', $course->id)->get();

        // Prepare data
        $data = $lectures->map(function ($lecture) use ($student) {
            $absence = Absence::where('student_id', $student->id)
                ->where('lecture_id', $lecture->id)
                ->first();

            $status = $absence && $absence->status === true ? 'Present' : 'Absent';

            return [
                'lecture_id'   => $lecture->id,
                'status'       => $status,
            ];
        });

        return response()->json($data);
    }
}
