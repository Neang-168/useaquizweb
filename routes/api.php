<?php

use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DegreeController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FacultyController;
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SemesterController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudySessionController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherAssignmentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TermController;
use App\Http\Controllers\Api\Teacher\CalendarController as TeacherCalendarController;
use App\Http\Controllers\Api\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Api\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Api\Teacher\FeedbackController as TeacherFeedbackController;
use App\Http\Controllers\Api\Teacher\QuestionController as TeacherQuestionController;
use App\Http\Controllers\Api\Teacher\QuestionImageUploadController as TeacherQuestionImageUploadController;
use App\Http\Controllers\Api\Teacher\QuestionImportExportController as TeacherQuestionImportExportController;
use App\Http\Controllers\Api\Teacher\QuizController as TeacherQuizController;
use App\Http\Controllers\Api\Teacher\ScoreController as TeacherScoreController;
use App\Http\Controllers\Api\Teacher\SubjectController as TeacherSubjectController;
use App\Http\Controllers\Api\Student\CalendarController as StudentCalendarController;
use App\Http\Controllers\Api\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Api\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Api\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Api\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Api\Student\ResultController as StudentResultController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Current authenticated user
    Route::get('/user', [UserController::class, 'user']);

    // Current user with role and permissions
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/me', [UserController::class, 'updateMe']);
    Route::post('/me/avatar', [UserController::class, 'uploadAvatar']);
    Route::put('/me/password', [UserController::class, 'changeMyPassword']);

    // SuperAdmin dashboard overview
    Route::get('/dashboard-stats', [DashboardController::class, 'index'])
        ->middleware('permission:manage_users');

    // Roles
    Route::get('/roles', [RoleController::class, 'index']);

    Route::get('/roles/{role}', [RoleController::class, 'show'])
        ->middleware('permission:manage_roles');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:manage_roles');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:manage_roles');

    // Users
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:manage_users');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:manage_users');

    Route::get('/users/next-username', [UserController::class, 'nextUsername'])
        ->middleware('permission:manage_users');

    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('permission:manage_users');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:manage_users');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:manage_users');

    Route::post('/users/{user}/avatar', [UserController::class, 'uploadUserAvatar'])
        ->middleware('permission:manage_users');

    // Academic structure: faculties, degrees, majors, subjects, academic years, shifts, stages, classes
    Route::middleware('permission:manage_academic_structure')->group(function () {
        Route::apiResource('faculties', FacultyController::class)->parameters(['faculties' => 'faculty']);
        Route::apiResource('departments', DepartmentController::class)->parameters(['departments' => 'department']);
        Route::apiResource('degrees', DegreeController::class)->parameters(['degrees' => 'degree']);
        Route::apiResource('majors', MajorController::class)->parameters(['majors' => 'major']);
        Route::get('/subjects/next-code', [SubjectController::class, 'nextCode']);
        Route::apiResource('subjects', SubjectController::class)->parameters(['subjects' => 'subject']);
        Route::apiResource('study-sessions', StudySessionController::class)->parameters(['study-sessions' => 'studySession']);

        Route::post('/academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent']);
        Route::apiResource('academic-years', AcademicYearController::class)->parameters(['academic-years' => 'academicYear']);

        Route::apiResource('semesters', SemesterController::class)->parameters(['semesters' => 'semester']);
        Route::apiResource('terms', TermController::class)->parameters(['terms' => 'term']);
        Route::apiResource('promotions', PromotionController::class)->parameters(['promotions' => 'promotion']);

        Route::apiResource('shifts', ShiftController::class)->parameters(['shifts' => 'shift']);
        Route::apiResource('stages', StageController::class)->parameters(['stages' => 'stage']);
        Route::get('/classes/{class}/roster', [ClassroomController::class, 'roster']);
        Route::get('/classes/{class}/export', [ClassroomController::class, 'exportResults']);
        Route::apiResource('classes', ClassroomController::class)->parameters(['classes' => 'class']);
    });

    // Teachers
    Route::apiResource('teachers', TeacherController::class)
        ->parameters(['teachers' => 'teacher'])
        ->middleware('permission:manage_teachers');

    // Teacher subject/class assignments (Admin manages what each teacher teaches)
    Route::apiResource('teacher-assignments', TeacherAssignmentController::class)
        ->parameters(['teacher-assignments' => 'teacherAssignment'])
        ->only(['index', 'store', 'destroy'])
        ->middleware('permission:manage_teachers');

    // Students
    Route::patch('students/{student}/assign', [StudentController::class, 'assign'])
        ->middleware('permission:manage_students');
    Route::apiResource('students', StudentController::class)
        ->parameters(['students' => 'student'])
        ->middleware('permission:manage_students');

    // Teacher's own workspace: classes, subjects, question bank, quizzes, scores, feedback
    Route::prefix('teacher')->middleware('permission:manage_exams')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index']);
        Route::get('/calendar', [TeacherCalendarController::class, 'index']);

        Route::get('/classes', [TeacherClassController::class, 'index']);

        Route::get('/subjects', [TeacherSubjectController::class, 'index']);

        Route::post('/uploads/question-image', [TeacherQuestionImageUploadController::class, 'store']);

        Route::get('/questions/template', [TeacherQuestionImportExportController::class, 'template']);
        Route::get('/questions/export', [TeacherQuestionImportExportController::class, 'export']);
        Route::post('/questions/import', [TeacherQuestionImportExportController::class, 'import']);

        Route::apiResource('questions', TeacherQuestionController::class)
            ->parameters(['questions' => 'question'])
            ->except(['show']);

        Route::post('/quizzes/{quiz}/publish', [TeacherQuizController::class, 'publish']);
        Route::post('/quizzes/{quiz}/close', [TeacherQuizController::class, 'close']);
        Route::apiResource('quizzes', TeacherQuizController::class)
            ->parameters(['quizzes' => 'quiz']);

        Route::get('/quizzes/{quiz}/scores', [TeacherScoreController::class, 'index']);
        Route::put('/submissions/{submission}/essay-score', [TeacherScoreController::class, 'gradeEssay']);

        Route::get('/quizzes/{quiz}/feedback', [TeacherFeedbackController::class, 'index']);
        Route::post('/feedback', [TeacherFeedbackController::class, 'store']);
    });

    // Student's own workspace: dashboard, courses, quizzes, results
    Route::prefix('student')->middleware('permission:take_exam')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index']);
        Route::get('/courses', [StudentCourseController::class, 'index']);
        Route::get('/calendar', [StudentCalendarController::class, 'index']);

        Route::get('/quizzes', [StudentQuizController::class, 'index']);
        Route::get('/quizzes/{quiz}', [StudentQuizController::class, 'show']);
        Route::post('/quizzes/{quiz}/submit', [StudentQuizController::class, 'submit']);

        Route::get('/results', [StudentResultController::class, 'index']);

        Route::get('/notifications', [StudentNotificationController::class, 'index']);
        Route::post('/notifications/{notification}/read', [StudentNotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [StudentNotificationController::class, 'markAllRead']);
        Route::delete('/notifications/clear-all', [StudentNotificationController::class, 'clearAll']);
        Route::delete('/notifications/{notification}', [StudentNotificationController::class, 'destroy']);
    });
});
