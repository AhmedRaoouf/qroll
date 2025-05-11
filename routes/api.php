<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    AdminController, DoctorController, TeacherController, StudentController,
    CourseController, SectionController, LectureController,
    StudentSectionController, StudentLectureController, AbsenceController,
    AuthController, ProfileController, RoleController, SectionAbsenceController, UserController
};

// ✅ Guest Routes (غير مسجل الدخول)
Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// 🔐 Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile',[ProfileController::class,'index']);
    Route::post('/profile/update',[ProfileController::class,'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);


    Route::apiResource('roles', RoleController::class);

    Route::apiResource('admins', AdminController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('teachers', TeacherController::class);
    Route::apiResource('students', StudentController::class);

    Route::apiResource('sections', SectionController::class);
    Route::apiResource('lectures', LectureController::class);

    Route::apiResource('users', UserController::class);


    // 📚 Courses
    Route::apiResource('courses', CourseController::class);
    Route::get('courses/{course}/students', [CourseController::class, 'students']);
    Route::get('courses/{course}/absent-students', [CourseController::class, 'absentStudents']);
    Route::post('courses/{course}/add-student', [CourseController::class, 'addStudent']);
    Route::get('courses/filter/by-lecture', [CourseController::class, 'filterByLecture']);
    Route::get('courses/filter/by-section', [CourseController::class, 'filterBySection']);

    // 🧩 Sections

    // 🎤 Lectures

    // 👥 Student in Section
    Route::apiResource('student-sections', StudentSectionController::class);

    // 🧑‍🎓 Student in Lecture
    Route::apiResource('student-lectures', StudentLectureController::class);

    // ❌ Lecture Absence
    Route::apiResource('absences', AbsenceController::class);

    // ❌ Section Absence
    Route::apiResource('section-absences', SectionAbsenceController::class);
});
