<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
// removed unused controllers
// use App\Http\Controllers\CompanyGroupController;
// use App\Http\Controllers\CompanyBranchController;
// use App\Http\Controllers\DeviceMasterController;
// use App\Http\Controllers\ReIdMasterController;
// use App\Http\Controllers\CctvLayoutController;
// use App\Http\Controllers\MonitoringController;
// use App\Http\Controllers\CctvLiveStreamController;
// use App\Http\Controllers\EventLogController;
// use App\Http\Controllers\ReportController;
// use App\Http\Controllers\ApiCredentialController;
// use App\Http\Controllers\BranchEventSettingController;
// use App\Http\Controllers\WhatsAppSettingsController;
use App\Http\Controllers\HpsElektronikController;
use App\Http\Controllers\HpsEmasController;
use App\Http\Controllers\FaqChatbotQnaController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Monitoring routes removed

// Horizon routes removed

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User CRUD
    Route::resource('users', UserController::class);

    // HPS Elektronik Import (place BEFORE resource to avoid {id} catching 'import')
    Route::get('/hps-elektronik/import', [HpsElektronikController::class, 'importForm'])->name('hps-elektronik.import.form');
    Route::get('/hps-elektronik/import/template', [HpsElektronikController::class, 'downloadTemplate'])->name('hps-elektronik.import.template');
    Route::post('/hps-elektronik/import', [HpsElektronikController::class, 'importStore'])->name('hps-elektronik.import.store');
    // HPS Elektronik CRUD
    Route::resource('hps-elektronik', HpsElektronikController::class);
    Route::post('/hps-elektronik/{hpsElektronik}/toggle', [HpsElektronikController::class, 'toggle'])->name('hps-elektronik.toggle');
    Route::get('/hps-elektronik/export/download', [HpsElektronikController::class, 'export'])->name('hps-elektronik.export');

    // HPS Emas Import (place BEFORE resource to avoid {id} catching 'import')
    Route::get('/hps-emas/import', [HpsEmasController::class, 'importForm'])->name('hps-emas.import.form');
    Route::get('/hps-emas/import/template', [HpsEmasController::class, 'downloadTemplate'])->name('hps-emas.import.template');
    Route::post('/hps-emas/import', [HpsEmasController::class, 'importStore'])->name('hps-emas.import.store');
    // HPS Emas Management (force parameter to 'hpsEmas' to avoid pluralization issues)
    Route::resource('hps-emas', HpsEmasController::class)
        ->parameters(['hps-emas' => 'hpsEmas']);
    Route::post('/hps-emas/{hpsEmas}/toggle', [HpsEmasController::class, 'toggle'])->name('hps-emas.toggle');
    Route::get('/hps-emas/export/download', [HpsEmasController::class, 'export'])->name('hps-emas.export');

    // FAQ Chatbot QnA Import (place BEFORE resource to avoid {id} catching 'import')
    Route::get('/faq-chatbot-qna/import', [FaqChatbotQnaController::class, 'importForm'])->name('faq-chatbot-qna.import.form');
    Route::get('/faq-chatbot-qna/import/template', [FaqChatbotQnaController::class, 'downloadTemplate'])->name('faq-chatbot-qna.import.template');
    Route::post('/faq-chatbot-qna/import', [FaqChatbotQnaController::class, 'importStore'])->name('faq-chatbot-qna.import.store');
    // FAQ Chatbot QnA CRUD
    Route::resource('faq-chatbot-qna', FaqChatbotQnaController::class);

    // Removed routes for modules that are no longer present
});
