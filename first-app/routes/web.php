<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\FlagController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportAIController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminCategoryController;

//Public Routes

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

//Authenticated User Routes

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Report submission
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/ai/suggest-report', [ReportAIController::class, 'suggestImprovements'])->name('ai.suggest-report');
    Route::get('/reports/my', [ReportController::class, 'myReports'])->name('reports.my');

    Route::post('/reports/{report}/vote',[VoteController::class, 'store'])->name('reports.vote');
    Route::post('/reports/{report}/flag', [FlagController::class, 'store'])->name('reports.flag');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Translation routes
    Route::post('/translate/text', [TranslationController::class, 'translateText'])->name('translate.text');
    Route::post('/translate/report', [TranslationController::class, 'translateReport'])->name('translate.report');

    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/language', [ProfileController::class, 'updateLanguage'])->name('profile.language.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/reports/pending', [AdminReportController::class, 'pending'])->name('admin.reports.pending');
    Route::get('/reports/verified', [AdminReportController::class, 'verified'])->name('admin.reports.verified');
    Route::post('/reports/{id}/approve', [AdminReportController::class, 'approve'])->name('admin.reports.approve');
    Route::post('/reports/{id}/reject', [AdminReportController::class, 'reject'])->name('admin.reports.reject.legacy');

    Route::get('/reports', [\App\Http\Controllers\Admin\ReportModerationController::class, 'index'])
        ->name('admin.reports');

    Route::post('/reports/{report}/verify', [\App\Http\Controllers\Admin\ReportModerationController::class, 'verify'])
        ->name('admin.reports.verify');

    Route::post('/reports/{report}/revision', [\App\Http\Controllers\Admin\ReportModerationController::class, 'requestRevision'])
        ->name('admin.reports.revision');

    Route::post('/reports/{report}/reject', [\App\Http\Controllers\Admin\ReportModerationController::class, 'reject'])
        ->name('admin.reports.reject');

    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/users/{user}/toggle-ban', [AdminUserController::class, 'toggleBan'])->name('admin.users.toggle-ban');
    Route::get('/audits', [AdminAuditLogController::class, 'index'])->name('admin.audits.index');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::patch('/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';