<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function myStats()
    {
        $user = Auth::user();
        $data = [];

        if ($user->student) {
            $data['courses_count'] = $user->student->courses()->count();
        }

        if ($user->doctor) {
            $data['courses_count'] = $user->doctor->courses()->count();
            $data['students_count'] = $user->doctor->courses()->withCount('students')->get()->sum('students_count');
        }

        if ($user->teacher) {
            $data['courses_count'] = $user->teacher->courses()->count();
            $data['students_count'] = $user->teacher->courses()->withCount('students')->get()->sum('students_count');
        }

        return response()->json($data);
    }
}
