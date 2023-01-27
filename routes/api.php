<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', [\App\Http\Controllers\UserController::class, 'login']);
Route::post('/forget-password', [\App\Http\Controllers\UserController::class, 'forgetPassword']);
Route::post('/check-code', [\App\Http\Controllers\UserController::class, 'checkResetCode']);
Route::post('/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword']);
Route::post('check-reset-password-code',[\App\Http\Controllers\UserController::class,'checkResetPasswordToken']);
Route::post('/logout', [\App\Http\Controllers\UserController::class, 'logout'])->middleware('auth:api', 'checkLanguage', 'role:Admin|Doctor|Patient|Reception');

Route::group(['prefix' => 'phones'], function () {
    Route::group(['middleware' => ['auth:api','checkLanguage','role:Admin|Doctor|Reception']], function () {
        Route::post('/delete', [\App\Http\Controllers\PhoneController::class, 'deletePhone']);
    });
});

Route::group(['prefix' => 'profile'], function () {
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Admin|Doctor|Patient|Reception']], function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, 'generalProfile']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Patient']], function () {
        Route::get('/patient', [\App\Http\Controllers\UserController::class, 'patientProfile']);
        Route::post('/edit-patient', [\App\Http\Controllers\UserController::class, 'editProfilePatient']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Doctor']], function () {
        Route::get('/doctor', [\App\Http\Controllers\UserController::class, 'doctorProfile']);
        Route::post('/edit-doctor', [\App\Http\Controllers\UserController::class, 'editProfileEmployee']);
    });
    Route::group(['middleware' => ['auth:api','checkLanguage','role:Reception']], function () {
        Route::get('/reception', [\App\Http\Controllers\UserController::class, 'receptionProfile']);
        Route::post('/edit-reception', [\App\Http\Controllers\UserController::class, 'editProfileEmployee']);
    });
});

Route::group(['prefix' => 'notifications'], function () {
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Admin|Patient|Doctor|Reception']], function () {
        Route::get('/isThere', [\App\Http\Controllers\NotificationController::class, 'isThereNotification']);
        Route::get('/get-all', [\App\Http\Controllers\NotificationController::class, 'getAllNotifications']);
        Route::post('/delete', [\App\Http\Controllers\NotificationController::class, 'deleteNotification']);
        Route::post('/get-notification', [\App\Http\Controllers\NotificationController::class, 'getSingleNotification']);
    });
});

Route::group(['prefix' => 'patients'], function () {
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Admin|Reception']], function () {
        Route::post('add', [\App\Http\Controllers\PatientController::class, 'addPatient']);
        Route::post('add-account', [\App\Http\Controllers\UserController::class, 'createPatientAccount']);
        Route::group(['prefix' => 'paginate'], function () {
            Route::get('get-all', [\App\Http\Controllers\PatientController::class, 'getAllPatients'])->defaults('type', 'Paginate');
        });
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Reception']], function () {
        Route::get('get-all-patients-reception',[\App\Http\Controllers\PatientController::class,'getAllPatientsReception']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Doctor']], function () {
        Route::get('get-patients', [\App\Http\Controllers\PatientController::class, 'getMyPatients']);
        Route::group(['prefix' => 'paginate'], function () {
            Route::get('get-patients', [\App\Http\Controllers\PatientController::class, 'getMyPatients'])->defaults('type', 'Paginate');
        });
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Doctor|Admin|Reception']], function () {
        Route::post('get-patient', [\App\Http\Controllers\PatientController::class, 'getSinglePatient']);
    });
});

Route::group(['prefix' => 'clinics'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Reception'], function () {
        Route::post('add', [\App\Http\Controllers\ClinicController::class, 'add']);
        Route::post('add-name', [\App\Http\Controllers\ClinicController::class, 'addName']);
        Route::get('get-names', [\App\Http\Controllers\ClinicController::class, 'getClinicsNames']);
        Route::get('get-all',[\App\Http\Controllers\ClinicController::class,'getClinics']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Patient|Reception'], function () {
        Route::post('get-clinics', [\App\Http\Controllers\ClinicController::class, 'getCLinicsByDepartmentId']);
        Route::post('search', [\App\Http\Controllers\ClinicController::class, 'search']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Doctor'], function () {
        Route::get('get-my-clinic', [\App\Http\Controllers\ClinicController::class, 'getMyClinic']);
    });
});

Route::group(['prefix' => 'analysis'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Doctor'], function () {
        Route::post('add-name', [\App\Http\Controllers\MedicalAnalysisController::class, 'AddAnalysisName']);
        Route::get('get-all', [\App\Http\Controllers\MedicalAnalysisController::class, 'getAllNames']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin'], function () {
        Route::post('delete-name', [\App\Http\Controllers\MedicalAnalysisController::class, 'DeleteAnalysisName']);
    });
});

Route::group(['prefix' => 'medicines'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Doctor'], function () {
        Route::post('add-name', [\App\Http\Controllers\MedicineController::class, 'AddMedicineName']);
        Route::get('get-all', [\App\Http\Controllers\MedicineController::class, 'getAllNames']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin'], function () {
        Route::post('delete-name', [\App\Http\Controllers\MedicineController::class, 'DeleteMedicineName']);
    });
});

Route::group(['prefix' => 'departments'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Patient|Reception'], function () {
        Route::get('get-all', [\App\Http\Controllers\DepartmentController::class, 'index']);
        Route::post('search', [\App\Http\Controllers\DepartmentController::class, 'search']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin'], function () {
        Route::post('add', [\App\Http\Controllers\DepartmentController::class, 'add']);
    });
});

Route::group(['prefix' => 'nurses'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin'], function () {
        Route::post('add', [\App\Http\Controllers\NurseController::class, 'add']);
        Route::post('add-account', [\App\Http\Controllers\UserController::class, 'createAccount'])->defaults('type', 'Nurse');
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Doctor'], function () {
        Route::post('get-nurse', [\App\Http\Controllers\NurseController::class, 'getSingleNurse']);
    });
});

Route::group(['prefix' => 'certificates'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin'], function () {
        Route::post('add', [\App\Http\Controllers\CertificateController::class, 'addCertificate']);
        Route::post('add-name', [\App\Http\Controllers\CertificateController::class, 'addCertificateName']);
        Route::post('add-source', [\App\Http\Controllers\CertificateController::class, 'addCertificateSource']);
        Route::post('get-name', [\App\Http\Controllers\CertificateController::class, 'getCertificatesNamesMenu']);
        Route::post('get-source', [\App\Http\Controllers\CertificateController::class, 'getCertificatesSourcesMenu']);
        Route::post('get-rating', [\App\Http\Controllers\CertificateController::class, 'getCertificatesRatingMenu']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Patient|Doctor'], function () {
        Route::post('get-all', [\App\Http\Controllers\CertificateController::class, 'getCertificates']);
    });
});

Route::group(['prefix' => 'countries'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin'], function () {
        Route::post('add', [\App\Http\Controllers\CountryController::class, 'addCountry']);
        Route::post('get-all', [\App\Http\Controllers\CountryController::class, 'getCountriesMenu']);
    });
});

Route::group(['prefix' => 'ratings'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Patient'], function () {
        Route::post('set', [\App\Http\Controllers\DoctorRatingsController::class, 'setRating']);
    });
});

Route::group(['prefix' => 'doctors', 'middleware' => 'auth:api'], function () {
    Route::post('get-available-dates', [\App\Http\Controllers\AppointmentController::class, 'getAvailableAppointmetsDates']);
    Route::post('get-available-apps', [\App\Http\Controllers\AppointmentController::class, 'getAvailableApps']);
    Route::group(['middleware' => 'checkLanguage', 'role:Admin|Patient|Reception'], function () {
        Route::get('get-all', [\App\Http\Controllers\DoctorController::class, 'index']);
        Route::post('get-doctors', [\App\Http\Controllers\DoctorController::class, 'getDoctorsByClinicId']);
        Route::post('search', [\App\Http\Controllers\DoctorController::class, 'search']);
    });
    Route::group(['middleware' => 'checkLanguage', 'role:Admin'], function () {
        Route::post('add', [\App\Http\Controllers\DoctorController::class, 'addDoctor']);
        Route::post('add-doctor-account', [\App\Http\Controllers\UserController::class, 'createAccount'])->defaults('type', 'Doctor')->name('create_doctor');
    });
    Route::group(['middleware' => 'checkLanguage', 'role:Admin|Patient|Doctor'], function () {
        Route::post('get-doctor', [\App\Http\Controllers\DoctorController::class, 'getSingleDoctor']);
    });
    Route::group(['middleware' => 'checkLanguage', 'role:Reception'], function () {
        Route::get('get-all-doctor-reception', [\App\Http\Controllers\DoctorController::class, 'getAllDoctorsReception']);
    });
});

Route::group(['prefix' => 'blog'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Doctor|Patient'], function () {
        Route::get('is-active', [\App\Http\Controllers\BlogController::class, 'isActive']);
        Route::get('get-last', [\App\Http\Controllers\BlogController::class, 'lastPost']);
        Route::get('get-departments', [\App\Http\Controllers\BlogController::class, 'getBlogDepartments']);
        Route::post('get-posts', [\App\Http\Controllers\BlogController::class, 'getPostsByDepartmentsId']);
        Route::post('get-post', [\App\Http\Controllers\BlogController::class, 'getSinglePost']);
        Route::post('like', [\App\Http\Controllers\BlogController::class, 'likePost']);
        Route::group(['prefix' => 'search'],function () {
            Route::post('departments', [\App\Http\Controllers\BlogController::class, 'searchBlogDepartments']);
            Route::post('posts', [\App\Http\Controllers\BlogController::class, 'searchPostsByDepartmentsId']);
        });

    });
    Route::group(['middleware' => 'auth:api', 'role:Doctor'], function () {
        Route::post('add', [\App\Http\Controllers\BlogController::class, 'addPost']);
        Route::post('edit', [\App\Http\Controllers\BlogController::class, 'editPost']);
        Route::get('get-my-posts', [\App\Http\Controllers\BlogController::class, 'getMyPosts']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Doctor'], function () {
        Route::post('delete', [\App\Http\Controllers\BlogController::class, 'deletePost']);
    });
});

Route::group(['prefix' => 'receptions'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin'], function () {
        Route::post('add', [\App\Http\Controllers\ReceptionController::class, 'add']);
        Route::post('add-account', [\App\Http\Controllers\UserController::class, 'createAccount'])->defaults('type', 'Reception');
    });
});
Route::group(['prefix' => 'setting'], function () {
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Admin|Doctor|Reception|Patient'], function () {
        Route::post('/change-lang', [\App\Http\Controllers\UserController::class, 'changeLanguage']);
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Doctor|Reception'], function () {
        Route::post('/send-malfunction', [\App\Http\Controllers\MalfunctionController::class, 'sendMalfunction']);
        Route::get('/get-malfunctions', [\App\Http\Controllers\MalfunctionController::class, 'getMyMalfunctions']);
        Route::get('/get-last', [\App\Http\Controllers\MalfunctionController::class, 'getLastMalfunction']);
        Route::post('/search-malfunctions', [\App\Http\Controllers\MalfunctionController::class, 'searchMyMalfunctions']);
        Route::group(['prefix' => 'paginate'], function () {
            Route::get('/get-malfunctions', [\App\Http\Controllers\MalfunctionController::class, 'getMyMalfunctions'])->defaults('type','Paginate');
            Route::post('/search-malfunctions', [\App\Http\Controllers\MalfunctionController::class, 'searchMyMalfunctions'])->defaults('type','Paginate');
        });
    });
    Route::group(['middleware' => 'auth:api', 'checkLanguage', 'role:Patient'], function () {
        Route::post('/send-report', [\App\Http\Controllers\UserController::class, 'sendReport']);
    });
});

Route::group(['prefix'=>'waitings'],function(){
    Route::group(['middleware'=>['auth:api','checkLanguage','role:Reception']],function(){
        Route::post('add-emergencie', [\App\Http\Controllers\WaitingController::class, 'addEmergencies']);
        Route::post('add-appointment-to-waitings',[\App\Http\Controllers\WaitingController::class,'addFromAppointment']);
    });
    Route::group(['middleware'=>['auth:api','checkLanguage','role:Reception|Doctor']],function(){
        Route::group(['prefix' => 'paginate'], function () {
            Route::post('get-waitings', [\App\Http\Controllers\WaitingController::class, 'getDoctorWaitings'])->defaults('type','Paginate');
        });
    });
    Route::group(['middleware'=>['auth:api','checkLanguage','role:Doctor']],function(){
        Route::get('get-last',[\App\Http\Controllers\WaitingController::class,'getLastWaiting']);
    });
});

Route::group(['prefix'=>'statistics'],function(){
    Route::group(['middleware'=>['auth:api','checkLanguage','role:Admin']],function(){
        Route::get('get-booking-source', [\App\Http\Controllers\StatisticsController::class, 'getbookingSourceRate']);
        Route::get('get-appointment-statues', [\App\Http\Controllers\StatisticsController::class, 'getAppointmentStatuesRate']);
        Route::get('get-simple-info', [\App\Http\Controllers\StatisticsController::class, 'getSimpleInfo']);
        Route::get('get-sessions-type', [\App\Http\Controllers\StatisticsController::class, 'getSessionsTypeCount']);
    });
});

Route::group(['prefix' => 'appointments'], function () {
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Patient']], function () {
        Route::get('get-calander-patient', [\App\Http\Controllers\AppointmentController::class, 'getCalander'])->defaults('type', 'Patient');
        Route::post('get-events-patient', [\App\Http\Controllers\AppointmentController::class, 'getEvents'])->defaults('type', 'Patient');
        Route::get('get-appointments-patient', [\App\Http\Controllers\AppointmentController::class, 'getMyAppointmentsPatient']);
        Route::get('get-reports-patient', [\App\Http\Controllers\AppointmentController::class, 'getMyReportsPatient']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Doctor']], function () {
        Route::get('get-calander-doctor', [\App\Http\Controllers\AppointmentController::class, 'getCalander'])->defaults('type', 'Doctor');
        Route::post('get-events-doctor', [\App\Http\Controllers\AppointmentController::class, 'getEvents'])->defaults('type', 'Doctor');
        Route::group(['prefix' => 'paginate'], function () {
            Route::get('get-appointments-doctor', [\App\Http\Controllers\AppointmentController::class, 'getMyAppointmentsDoctor'])->defaults('type', 'Paginate');
            Route::get('get-reports-doctor', [\App\Http\Controllers\AppointmentController::class, 'getMyReportsDoctor'])->defaults('type', 'Paginate');
            Route::get('get-today-reports', [\App\Http\Controllers\AppointmentController::class, 'getTodayReports'])->defaults('type', 'Paginate');
        });
        Route::get('get-appointments-doctor', [\App\Http\Controllers\AppointmentController::class, 'getMyAppointmentsDoctor']);
        Route::get('get-reports-doctor', [\App\Http\Controllers\AppointmentController::class, 'getMyReportsDoctor']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Admin|Reception|Doctor']], function () {
        Route::group(['prefix' => 'paginate'], function () {
            Route::post('get-appointments', [\App\Http\Controllers\AppointmentController::class, 'getAppointmentsByPatientId'])->defaults('type', 'Paginate');
            Route::post('get-reports', [\App\Http\Controllers\AppointmentController::class, 'getReportsByPatientId'])->defaults('type', 'Paginate');
        });
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Admin|Doctor|Reception|Patient']], function () {
        Route::post('get-report', [\App\Http\Controllers\AppointmentController::class, 'getSingleReport']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Patient|Reception']], function () {
        Route::post('cancel', [\App\Http\Controllers\AppointmentController::class, 'cancelAppointment']);
        Route::post('book', [\App\Http\Controllers\AppointmentController::class, 'book']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Reception']], function () {
        Route::group(['prefix' => 'paginate'], function () {
            Route::get('get-today-appointments', [\App\Http\Controllers\AppointmentController::class, 'getTodayAppointments'])->defaults('type', 'Paginate');
        });
    });
});

Route::group(['prefix' => 'sessions'], function () {
    Route::group(['middleware'=>['auth:api', 'checkLanguage', 'role:Admin']],function(){
        Route::get('get-session-durations',[\App\Http\Controllers\SessionController::class,'getSessionDurationMenu']);
    });
    Route::group(['middleware' => ['auth:api', 'checkLanguage', 'role:Doctor']], function () {
        Route::post('open', [\App\Http\Controllers\SessionController::class, 'openSession']);
        Route::post('create', [\App\Http\Controllers\SessionController::class, 'createSession']);
        Route::post('get-titles', [\App\Http\Controllers\SessionController::class, 'getSessionsTitle']);
    });
});

Route::group(['prefix' => 'calculations'], function () {
    Route::group(['middleware'=>['auth:api', 'checkLanguage', 'role:Reception']],function(){
        Route::group(['prefix' => 'paginate'], function () {
            Route::get('get-all', [\App\Http\Controllers\SessionCalculationController::class, 'getAllSessionsCalculations'])->defaults('type', 'Paginate');
        });
        Route::post('make-paid',[\App\Http\Controllers\SessionCalculationController::class,'makePaided']);
    });
});

Route::group(['prefix' => 'employees'], function () {
    Route::group(['middleware' => 'auth:api', 'role:Admin'], function () {
        Route::post('add-working-times', [\App\Http\Controllers\EmployeeController::class, 'assignWorkTimes']);
        Route::post('get-workingTimes-by-date', [\App\Http\Controllers\EmployeeController::class, 'getWorkingTimeInDay']);
    });
    Route::post('get-working-times', [\App\Http\Controllers\EmployeeController::class, 'getWorkTimes'])->middleware('auth:api');
    Route::group(['middleware' => 'auth:api', 'role:Doctor|Nurse|Reception|Admin'], function () {
        // Route::get('get-workingTimes',[\App\Http\Controllers\EmployeeController::class,'getWorkTimes']);
    });
});
Route::group(['prefix'=>'payments'],function(){
    Route::group(['middleware'=>['auth:api', 'checkLanguage', 'role:Admin']],function(){
        Route::post('add',[\App\Http\Controllers\PaymentController::class,'add']);

    });
    Route::group(['middleware'=>['auth:api', 'checkLanguage', 'role:Reception|Doctor|Nurse']],function(){
        Route::get('get-balance',[\App\Http\Controllers\PaymentController::class,'getBalance']);
    });
});
Route::get('attendance/{id}',[\App\Http\Controllers\AttendanceController::class,'attendances'])->middleware(['auth:api','role:Reception']);
