<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function myStats()
    {
        $user = Auth::guard('api')->user();
        $data = [];

        if ($user->hasRole('admin')) {
            $data = [
                'doctors_count' => \App\Models\Doctor::count(),
                'teachers_count' => \App\Models\Teacher::count(),
                'courses_count' => \App\Models\Course::count(),
                'lectures_count' => \App\Models\Lecture::count(),
                'students_count' => \App\Models\Student::count(),
            ];
        } elseif ($user->student) {
            $data['courses_count'] = $user->student->courses()->count();
        } elseif ($user->doctor) {
            $data['courses_count'] = $user->doctor->courses()->count();
            $data['students_count'] = $user->doctor->courses()->withCount('students')->get()->sum('students_count');
        } elseif ($user->teacher) {
            $data['courses_count'] = $user->teacher->courses()->count();
            $data['students_count'] = $user->teacher->courses()->withCount('students')->get()->sum('students_count');
        }

        return response()->json(['status' => 200, 'data' => $data]);
    }
}
