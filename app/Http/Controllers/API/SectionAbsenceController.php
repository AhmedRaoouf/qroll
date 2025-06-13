<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Course;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentSection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SectionAbsenceController extends Controller
{
    public function getAbsencesByCourse(string $id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['error' => 'Course not found'], 404);
        }
        $students = Student::whereHas('sections.course', function ($q) use ($course) {
            $q->where('id', $course->id);
        })->get();

        $sections = Section::where('course_id', $course->id)->pluck('id');

        $data = $students->map(function ($student) use ($sections) {
            $absentsections = StudentSection::where('student_id', $student->id)
                ->whereIn('section_id', $sections)
                ->first();

            $totalsections = $sections->count();
            $absentCount = $absentsections->count();
            $absencePercentage = $totalsections > 0
                ? round(($absentCount / $totalsections) * 100)
                : 0;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'academic_id' => $student->academic_id,
                'number_of_absence' => $absentCount,
                'lecture_numbers' => $absentsections->implode(','),
                'absence_percentage' => "{$absencePercentage}%",
            ];
        });

        return response()->json($data);
    }


    public function getAbsencesBySection(string $courseId, string $sectionId)
    {
        $course = Course::find($courseId);
        if (! $course) {
            return response()->json(['error' => 'Course not found'], 404);
        }
        $section = Section::find($sectionId);
        if ($section?->course_id !== $course?->id) {
            return response()->json(['message' => 'Section does not belong to this course.'], 404);
        }

        $students = Student::whereHas('sections', function ($q) use ($section, $course) {
            $q->where('sections.id', $section->id)
                ->where('sections.course_id', $course->id);
        })->get();

        $data = $students->map(function ($student) use ($section) {
            $absence = StudentSection::where('student_id', $student->id)
                ->where('section_id', $section->id)
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

    public function getStudentSectionAbsences(string $id)
    {
        $student = Auth::guard('api')->user()->student;
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['error' => 'Course not found'], 404);
        }
        if (!$student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }
        $sections = Section::where('course_id', $course->id)->get();

        $data = $sections->map(function ($section) use ($student) {
            $absence = StudentSection::where('student_id', $student->id)
                ->where('section_id', $section->id)
                ->first();

            $status = $absence && $absence->status === true ? 'Present' : 'Absent';
            return [
                'section'   => $section,
                'status'       => $status,
            ];
        });

        return response()->json($data);
    }
}
