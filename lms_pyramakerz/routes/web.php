<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\EbookController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LessonResourceController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MaterialResourceController;
use App\Http\Controllers\Admin\ObserverController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeacherResourceController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ChapterController as ControllersChapterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\SchoolTypeController;
use App\Http\Controllers\Student\PasswordStudentController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\Teacher\TeacherClasses;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherResources;
use App\Http\Controllers\Teacher\TeacherUnitController;
use App\Http\Controllers\Student\StudentAssignmentController;
use App\Http\Controllers\Observer\ObserverDashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UnitController as ControllersUnitController;
use App\Models\Group;
use App\Models\School;
use App\Models\Stage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('landing'); // Displays the landing page
})->name('landing');
Route::get('/chats', [ChatController::class, 'viewAllChats'])->name('chat.all');

Route::get('/chat/{receiverId}/{receiverType}', [ChatController::class, 'chatForm'])->name('chat.form');
Route::post('/chat/{receiverId}/{receiverType}', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/chat/{receiverId}/{receiverType}/messages', [ChatController::class, 'fetchMessages']);


Route::get('/chat/{receiverId}/{receiverType}', [ChatController::class, 'chatForm'])->name('chat.form');
Route::post('/chat/{receiverId}/{receiverType}', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/chat/{receiverId}/{receiverType}/messages', [ChatController::class, 'fetchMessages']);





Route::prefix('admin')->group(function () {
    Route::get('/get-students-school/{school}', [ReportController::class, 'getSchoolStudents'])->name('getSchoolStudents');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/loginP', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('admin.login.post');
    Route::middleware('auth:admin,web')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

        // Admin Controller 
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('stage/{stageId}/material/create', [StageController::class, 'createMaterial'])->name('material.unit.chapter.create');

        Route::resource('material', MaterialController::class);
        Route::resource('units', UnitController::class);
        Route::resource('chapters', ChapterController::class);
        Route::resource('lessons', LessonController::class);
        Route::resource('stages', StageController::class);
        Route::resource('lesson_resource', LessonResourceController::class);
        Route::get('/lesson-resource/schools/{lessonId}', [LessonResourceController::class, 'getSchoolsByLesson'])
            ->name('lesson_resource.schools');

        Route::resource('theme_resource', MaterialResourceController::class);

        Route::get('/theme-resource/schools/{themeId}', [MaterialResourceController::class, 'getSchoolsByTheme'])
            ->name('theme_resource.schools');

        Route::get('/theme-resource/themes/{stageId}', [MaterialResourceController::class, 'getThemesByStage'])
            ->name('theme_resource.themes');

        Route::get('/resources/view/{resource}/{type}', [MaterialResourceController::class, 'viewResource'])->name('admin.resources.view');

        // Route::resource('assignments', AssignmentController::class);
        Route::resource('ebooks', EbookController::class);
        Route::resource('classes', ClassController::class);
        Route::post('/classes/promote-multiple', [ClassController::class, 'promoteMultiple'])->name('classes.promoteMultiple');

        Route::get('/lessons/{lesson}/view', [LessonController::class, 'viewEbook'])->name('lesson.view');

        Route::resource('students', StudentController::class);
        // Route::get('admin/students/promote', [StudentController::class, 'promoteView'])->name('students.promote.view');
        Route::delete('/admin/students/delete-multiple', [StudentController::class, 'deleteMultiple'])->name('students.deleteMultiple');
        Route::post('/students/bulk-update', [StudentController::class, 'bulkUpdate'])->name('students.bulkUpdate');
        Route::resource('teachers', TeacherController::class);

        Route::get('observers/observation_questions', [ObserverController::class, 'addQuestions'])->name('observers.addQuestions');
        Route::get('observers/observation_report', [ObserverController::class, 'observationReport'])->name('observers.obsReport');
        Route::delete('/questions/{id}', [ObserverController::class, 'deleteQuestion'])->name('questions.destroy');
        // Routes for questions and headers
        Route::post('/template/store', [ObserverController::class, 'storeTemplate'])->name('templates.store');
        Route::post('/questions/store', [ObserverController::class, 'storeQuestion'])->name('questions.storeQuestion');
        Route::delete('/headers/{id}', [ObserverController::class, 'deleteHeader'])->name('headers.deleteHeader');
        Route::post('/headers/store', [ObserverController::class, 'storeHeader'])->name('headers.storeHeader');
        Route::post('/headers/edit', [ObserverController::class, 'editHeader'])->name('headers.editHeader');
        Route::post('/questions/edit', [ObserverController::class, 'editQuestion'])->name('questions.editQuestion');


        Route::resource('observers', ObserverController::class);
        Route::resource('admins', AdminController::class);
        Route::resource('images', ImageController::class);
        Route::resource('types', TypeController::class);
        Route::resource('teacher_resources', TeacherResourceController::class);
        Route::get('reports/assignment_avg_report', [ReportController::class, 'assignmentAvgReport'])->name('admin.assignmentAvgReport');
        Route::get('reports/assessment_report', [ReportController::class, 'assessmentReport'])->name('admin.assesmentReport');
        Route::get('reports/login_report', [ReportController::class, 'loginReport'])->name('admin.loginReport');
        Route::get('reports/compare_report', [ReportController::class, 'compareReport'])->name('admin.compareReport');
        Route::get('/get-teachers-school/{schoolId}', [ReportController::class, 'getSchoolTeachers'])->name('getSchoolTeachers');
        Route::get('/get-grades-school/{schoolId}', [ReportController::class, 'getSchoolGrades'])->name('getSchoolGrades');
        Route::get('/get-classes-school/{schoolId}', [ReportController::class, 'getSchoolClasses'])->name('getSchoolClasses');
        Route::get('/teacher-schools/{teacherId}', [TeacherController::class, 'addSchool'])->name('teachers.addSchool');
        Route::post('/teacher-schools/store', [TeacherController::class, 'storeSchool'])->name('teachers.storeSchool');


        Route::get('school/{schoolId}/curriculum', [AdminController::class, 'assignCurriculum'])->name('school.curriculum.assign');
        Route::post('school/{schoolId}/curriculum', [AdminController::class, 'storeCurriculum'])->name('school.curriculum.store');
        Route::get('school/{schoolId}/curriculum/view', [AdminController::class, 'viewCurriculum'])->name('school.curriculum.view');
        Route::delete('/schools/{schoolId}/stages/{stageId}', [AdminController::class, 'removeStage'])->name('school.removeStage');
        Route::delete('/schools/{schoolId}/materials/{materialId}', [AdminController::class, 'removeMaterial'])->name('school.removeMaterial');
        Route::delete('/schools/{schoolId}/units/{unitId}', [AdminController::class, 'removeUnit'])->name('school.removeUnit');
        Route::delete('/schools/{schoolId}/chapters/{chapterId}', [AdminController::class, 'removeChapter'])->name('school.removeChapter');
        Route::delete('/schools/{schoolId}/lessons/{lessonId}', [AdminController::class, 'removeLesson'])->name('school.removeLesson');
        // Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('classes/{id}/import', [ClassController::class, 'showImportForm'])->name('classes.import');
        Route::post('classes/{id}/import', [ClassController::class, 'importStudents'])->name('classes.importStudents');
        Route::get('classes/{id}/export', [ClassController::class, 'exportStudents'])->name('classes.export');

        Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::put('/admin/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('admin.tickets.updateStatus');


        Route::post('teachers/generate', [TeacherController::class, 'generate'])->name('teachers.generate');

        Route::get('/api/schools/{school}/stages', function (School $school) {
            return response()->json($school->stages);
        })->name('admin.schools.stages');

        Route::get('/api/stages/{stage}/students', function (Stage $stage) {
            return response()->json($stage->students);
        });

        Route::get('/api/stages/{stage}/classes', function (Stage $stage) {
            return response()->json($stage->classes);
        })->name('admin.stages.classes');
        // Route::get('/admin/schools/{school}/stages', [StudentController::class, 'getStages'])
        //     ->name('admin.schools.stages');
        Route::get('/admin/schools/{school}/stages/{stage}/classes', [StudentController::class, 'getClasses'])->name('admin.schools.stages.classes');

        Route::delete('/roles/{role}/permissions/{permission}', [RoleController::class, 'revokePermission'])->name('roles.permissions.revoke');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'givePermission'])->name('roles.permissions');

        Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('admin.roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');

        Route::get('/roles/assign', [RoleController::class, 'assignRole'])->name('admin.roles.assign');
        Route::post('/roles/assign', [RoleController::class, 'assignRoleToUser'])->name('admin.roles.assignUser');



        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');


        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/roles', [UserController::class, 'assignRole'])->name('users.roles');
        Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.roles.remove');
        Route::post('/users/{user}/permissions', [UserController::class, 'givePermission'])->name('users.permissions');
        Route::delete('/users/{user}/permissions/{permission}', [UserController::class, 'revokePermission'])->name('users.permissions.revoke');


        Route::get('/api/schools/{school}/classes', function (School $school) {
            return response()->json($school->classes);
        })->name('admin.schools.classes');
    });
});

Route::post('/pusher/auth', [PusherController::class, 'auth'])->name('pusher.auth');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Student dashboard route with 'auth:student' middleware
Route::get('/student/dashboard', [DashboardController::class, 'index'])->middleware('auth:student')->name('student.dashboard');

// Start student  dashboard routes

Route::get('/theme', [DashboardController::class, 'theme'])->name('student.theme');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('student.index');

Route::get('/materials/{materialId}/units', [ControllersUnitController::class, 'index'])->name('student_units.index');
Route::get('/units/{unitId}', [ControllersUnitController::class, 'unitContent'])->name('student_units.unitContent');
Route::get('/units/{unitId}/chapters', [ControllersChapterController::class, 'index'])->name('student_chapters.index');

Route::get('/chapters/{chapterId}/lessons', [ControllersChapterController::class, 'showLessons'])->name('student_lessons.index');
// Route::get('/student/themes/{themeId}/units/{unitId}/chapters', [ControllersChapterController::class, 'index'])
//     ->name('student.chapters.index');

Route::get('/lessons/{lessonId}/ebooks', [ControllersChapterController::class, 'viewEbooks'])->name('student_lessons.ebooks');

Route::post('changeStudentPassword', [PasswordStudentController::class, 'changePassword'])->name('changeStudentPassword');

// End student  dashboard routes



// Teacher dashboard route with 'auth:teacher' middleware
Route::get('/teacher/dashboard', function () {
    return 'Teacher Dashboard';
})->middleware('auth:teacher')->name('teacher.dashboard');


// Route::get('/observer/dashboard', function () {
//     return 'Observer Dashboard';
// })->middleware('auth:observer')->name('observer.dashboard');


Route::get('/assignment', [StudentAssignmentController::class, 'index'])->name('student.assignment');
Route::get('/assignment_show/{assignmentID}', [StudentAssignmentController::class, 'show'])->name('student.assignment.show');
Route::post('/answer_assignment', [StudentAssignmentController::class, 'store'])->name('student.assignment.store');

Route::get('/create_theme', function () {
    return view('pages.teacher.theme.create');
});

Route::get('/create_unit', function () {
    return view('pages.teacher.unit.create');
});

Route::get('/create_material', function () {
    return view('pages.teacher.material.create');
});

Route::get('/create_chapter', function () {
    return view('pages.teacher.chapter.create');
});

Route::get('/create_lesson', function () {
    return view('pages.teacher.lesson.create');
});

Route::prefix('teacher')->middleware('auth:teacher')->group(function () {
    Route::resource('assessments', StudentAssessmentController::class);
    // Route::get('teacher_classes', [TeacherClasses::class, 'index'])->name('teacher_classes');


    Route::get('/teacher/resources', [TeacherResources::class, 'index'])->name('teacher.resources.index');
    Route::get('/teacher/resources/admin', [TeacherResources::class, 'adminResources'])->name('teacher.resources.admin');
    // web.php
    Route::get('/resources/view/{resource}/{type}', [TeacherResources::class, 'viewResource'])->name('teacher.resources.view');

    Route::get('/teacher/resources/create', [TeacherResources::class, 'create'])->name('teacher.resources.create');
    Route::get('/teacher/resources/{id}/edit', [TeacherResources::class, 'edit'])->name('teacher.resources.edit');
    Route::post('/teacher/resources', [TeacherResources::class, 'store'])->name('teacher.resources.store');
    Route::put('/teacher/resources/{id}', [TeacherResources::class, 'update'])->name('teacher.resources.update');
    Route::delete('/teacher/resources/{id}', [TeacherResources::class, 'destroy'])->name('teacher.resources.destroy');
    // Route::post('/lesson-resource/download/', [LessonResourceController::class, 'download'])->name('lesson_resource.download');
    // Route::post('/material-resource/download/', [MaterialResourceController::class, 'download'])->name('theme_resource.download');
    // Route::post('/teacher/download-resources', [MaterialResourceController::class, 'downloadResources'])->name('download.resources');

    // Route::post('/material-resource/download/', [MaterialResourceController::class, 'downloadThemeResources'])->name('theme_resource.download');

    Route::post('/teacher/download-resources', [MaterialResourceController::class, 'downloadResources'])->name('download.resources');



    Route::get('/teacher/classes/{stage_id}', [TeacherClasses::class, 'index'])->name('teacher_classes');

    Route::get('students_classess/{class_id}', [TeacherClasses::class, 'students'])->name('students_classess');
    Route::post('store-assessment', [TeacherClasses::class, 'storeAssessment'])->name('teacher.storeAssessment');
    Route::get('/tickets', [App\Http\Controllers\Teacher\TicketController::class, 'index'])->name('teacher.tickets.index');
    Route::get('/tickets/create', [App\Http\Controllers\Teacher\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [App\Http\Controllers\Teacher\TicketController::class, 'store'])->name('tickets.store');

    Route::get('show-assignments/{id}', [\App\Http\Controllers\Teacher\AssignmentController::class, 'showAssignments'])->name('assignments.showAssignments');
    Route::get('assignments/create/{stageId?}', [\App\Http\Controllers\Teacher\AssignmentController::class, 'create'])->name('assignments.create');
    Route::resource('assignments', \App\Http\Controllers\Teacher\AssignmentController::class);

    Route::get('teacher/api/stages/{stageId}/classes', [\App\Http\Controllers\Teacher\AssignmentController::class, 'getClassesByStage'])
        ->name('teacher.get-classes');





    Route::get('assessments/student/{student_id}', [StudentAssessmentController::class, 'showStudentAssessments'])->name('teacher.assessments.student');

    Route::get('assignments/{id}/students', [\App\Http\Controllers\Teacher\AssignmentController::class, 'viewAssignedStudents'])->name('assignments.students');
    Route::post('assignments/{id}/students/{studentId}/update', [\App\Http\Controllers\Teacher\AssignmentController::class, 'updateStudentMarks'])->name('assignments.students.update');

    Route::get('/api/schools/{school}/stages', function (School $school) {
        return response()->json($school->stages);
    });
    Route::post('changename', [TeacherDashboardController::class, 'changeName'])->name('changename');

    // Route::get('/teacher.assignment', [StudentAssignmentController::class, 'index'])->name('teacher.Assignment');
    //     return view('pages.teacher.Assignment.index');
    // })->name('teacher.Assignment');

    Route::get('/api/stages/{stage}/students', function (Stage $stage) {
        return response()->json($stage->students);
    });

    // Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');

    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');

    // Route::resource('assignments', \App\Http\Controllers\Teacher\AssignmentController::class);



    Route::get('/teacher/stage/{stageId}/materials', [TeacherDashboardController::class, 'showMaterials'])->name('teacher.showMaterials');
    Route::get('/teacher/material/{materialId}/units', [TeacherDashboardController::class, 'showUnits'])->name('teacher.units');
    Route::get('/units/{unitId}/chapters', [TeacherDashboardController::class, 'showChapters'])->name('teacher.chapters.index');
    Route::get('/chapters/{chapterId}/lessons', [TeacherDashboardController::class, 'showLessons'])->name('teacher.lessons.index');


    Route::get('/teacher/info/{id}', function ($id) {
        return view('pages.teacher.info', compact('id'));
    })->name('teacher.info');

    Route::get('/teacher/theme', function () {
        return view('pages.teacher.teacherTheme');
    })->name('teacher.TTheme');
});
Route::prefix('observer')->middleware('auth:observer')->group(function () {
    Route::get('/dashboard', [ObserverDashboardController::class, 'index'])->name('observer.dashboard');

    // Route::get('/observations/export', [ObserverDashboardController::class, 'exportObservations'])->name('observer.observations.export');

    Route::get('/observations/export', [ObserverDashboardController::class, 'exportObservations'])
        ->name('observer.observations.export');

    Route::get('/observation/{id}/export', [ObserverDashboardController::class, 'exportSingleObservation'])
        ->name('observer.observation.export');

    Route::get('/teacher_resources', [ObserverDashboardController::class, 'showTeacherResources'])
        ->name('observer.teacherResources');

    Route::get('/admin_resources', [ObserverDashboardController::class, 'showAdminResources'])
        ->name('observer.admin_resources');

    Route::get('/resources/view/{resource}/{type}', [ObserverDashboardController::class, 'viewResource'])->name('observer.resources.view');





    Route::get('/report', [ObserverDashboardController::class, 'report'])->name('observer.report');

    Route::get('/observation/create', [ObserverDashboardController::class, 'createObservation'])->name('observer.observation.create');
    Route::get('/observation/get_school/{teacher_id}', [ObserverDashboardController::class, 'getSchool'])->name('observer.observation.getSchool');
    Route::get('/observation/get_coteachers/{school_id}', [ObserverDashboardController::class, 'getCoteachers'])->name('observer.observation.getCoteachers');

    Route::get('/observation/get_stages/{teacher_id}', [ObserverDashboardController::class, 'getStages'])->name('observer.observation.getStages');
    Route::post('/observation/store/', [ObserverDashboardController::class, 'store'])->name('observation.store');
    Route::delete('/observation/delete/{id}', [ObserverDashboardController::class, 'destroy'])->name('observation.destroy');
    Route::get('/observation/view/{id}', [ObserverDashboardController::class, 'view'])->name('observation.view');
});








Route::get('/create_assignment', function () {
    return view('pages.teacher.Assignment.create');
})->name('teacher.Assignment.create');


Route::get('/view_class', function () {
    return view('pages.teacher.Class.index');
})->name('teacher.class');

Route::get('/view_student_grade', function () {
    return view('components.GradesTable');
})->name('teacher.student.grade');


Route::get('/view_student_gradessss', function () {
    return view('components.GradeTableForOneStudent');
})->name('teacher.student.grade');


Route::get('/Show_Assignment', function () {
    return view('pages.teacher.Assignment.details');
})->name('teacher.assignment.show');

Route::get('/Edit_Assignment', function () {
    return view('pages.teacher.Assignment.Edit');
})->name('teacher.assignment.edit');


Route::get('/view_material_in_grade', function () {
    return view('pages.teacher.material.index');
})->name('teacher.material');

Route::get('/view_teacher_theme', function () {
    return view('pages.teacher.theme.index');
})->name('teacher.theme');

Route::get('/view_teacher_unit', function () {
    return view('pages.teacher.unit.index');
})->name('teacher.unit');

Route::get('/view_chapter', function () {
    return view('pages.teacher.chapter.index');
})->name('teacher.chapter');

Route::get('/view_lesson', function () {
    return view('pages.teacher.lesson.index');
})->name('teacher.lesson');

Route::get('/view_assignments_cards', function () {
    return view('pages.teacher.AssignmentsCards.index');
})->name('teacher.assignments_cards');
// Route::group(['middleware' => ['admin:super_admin,school_admin']], function () {

// });
