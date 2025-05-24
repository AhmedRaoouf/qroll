<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    AdminController,
    DoctorController,
    TeacherController,
    StudentController,
    CourseController,
    SectionController,
    LectureController,
    AuthController,
    ProfileController,
    RoleController,
    SectionAbsenceController,
    DashboardController,
    LectureAbsenceController
};

// 🟡 Guest Routes
Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// 🔐 Authenticated Routes
Route::middleware('api-auth')->group(function () {
    // 🧑‍💼 Profile
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // 🟢 My Info (General Routes for all users)
    Route::get('/my-courses', [CourseController::class, 'myCourses']);
    Route::get('/my-stats', [DashboardController::class, 'myStats']);

    Route::get('courses/{course}/sections', [CourseController::class, 'sections']);
    Route::get('courses/{course}/lectures', [CourseController::class, 'lectures']);

    // 🛠️ Admin Routes
    Route::prefix('admin')->middleware(['check.role:admin'])->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('admins', AdminController::class);
        Route::apiResource('doctors', DoctorController::class);
        Route::apiResource('teachers', TeacherController::class);
        Route::apiResource('students', StudentController::class);
        Route::post('students/{student}/add-courses', [StudentController::class, 'addCourses']);
        Route::apiResource('sections', SectionController::class);
        Route::apiResource('lectures', LectureController::class);
        Route::apiResource('courses', CourseController::class);
    });

    Route::middleware(['auth:sanctum', 'check.role:admin,doctor,teacher'])->group(function () {
        Route::get('courses/{course}/students', [CourseController::class, 'allStudents']);
        Route::post('courses/{course}/add-student', [CourseController::class, 'addStudentToCourse']);
        Route::post('courses/{course}/add-student-to-section', [CourseController::class, 'addStudentToSections']);
        Route::get('lectures/{lecture}/generate-qr', [LectureController::class, 'generateQR']);
        Route::get('sections/{section}/generate-qr', [SectionController::class, 'generateQR']);

    });

    // 👨‍🎓 Student Routes
    Route::prefix('student')->middleware(['check.role:student'])->group(function () {
        Route::get('courses/{course}/lecture-attendance', [LectureAbsenceController::class, 'getStudentLectureAbsences']);
        Route::get('courses/{course}/section-attendance', [SectionAbsenceController::class, 'getStudentSectionAbsences']);
        Route::post('student/lecture-attendance/scan', [LectureController::class, 'scanQR']);
        Route::post('student/section-attendance/scan', [SectionController::class, 'scanQR']);
    });

    // 📊 Absence Reports
    Route::get('courses/{course}/excessive-absence/lectures', [LectureAbsenceController::class, 'getAbsencesByCourse']);
    Route::get('courses/{course}/excessive-absence/sections', [SectionAbsenceController::class, 'getAbsencesByCourse']);
    Route::get('courses/{course}/lectures/{lecture}/attendance', [LectureAbsenceController::class, 'getAbsencesByLecture']);
    Route::get('courses/{course}/sections/{section}/attendance', [SectionAbsenceController::class, 'getAbsencesBySection']);
});
