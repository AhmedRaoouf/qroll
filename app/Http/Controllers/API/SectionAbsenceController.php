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

        // Get students registered in this course through sections
        $students = Student::whereHas('sections.course', function ($q) use ($course) {
            $q->where('id', $course->id);
        })->get();

        // Get all section IDs related to the course
        $sectionIds = Section::where('course_id', $course->id)->pluck('id');

        // Prepare data per student
        $data = $students->map(function ($student) use ($sectionIds) {
            // Get all section records for this student in this course
            $studentSections = StudentSection::where('student_id', $student->id)
                ->whereIn('section_id', $sectionIds)
                ->get();

            // You can filter by status here if needed, e.g. where('status', 'absent')
            $absentCount = $studentSections->count();
            $totalSections = $sectionIds->count();

            $absencePercentage = $totalSections > 0
                ? round(($absentCount / $totalSections) * 100)
                : 0;

            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'academic_id' => $student->academic_id,
                'number_of_absence' => $absentCount,
                'lecture_numbers' => $studentSections->pluck('section_id')->implode(','), // e.g., 5,7,9
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
                'name' => $student->user->name,
                'academic_id' => $student->academic_id,
                'date' => $section->created_at->format('d-m-Y'),
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
