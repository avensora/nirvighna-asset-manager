<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduledExpenseController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.store');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email verification
    Route::get('/verify-email', [VerifyEmailController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // All verified-user routes
    Route::middleware('verified')->group(function () {
        Route::get('/', fn () => redirect()->route('dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile (all users)
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Clients — all users
        Route::resource('clients', ClientController::class);

        // Invoices — all users
        Route::resource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/send',       [InvoiceController::class, 'sendEmail'])->name('invoices.send');
        Route::post('/invoices/{invoice}/mark-paid',  [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::post('/invoices/{invoice}/mark-unpaid',[InvoiceController::class, 'markUnpaid'])->name('invoices.mark-unpaid');
        Route::get('/invoices/{invoice}/pdf',         [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

        // Calendar — all users
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
        Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
        Route::patch('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
        Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

        // Chat — all verified users
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
        Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:30,1')->name('chat.store');
        Route::get('/chat/attachments/{message}', [ChatController::class, 'attachment'])->name('chat.attachment');

        // Manager-only routes
        Route::middleware('role:manager')->group(function () {
            // Finances (Phase 4)
            Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
            Route::resource('transactions', TransactionController::class)->except(['show']);
            Route::post('/scheduled-expenses/{scheduledExpense}/pay', [ScheduledExpenseController::class, 'pay'])->name('scheduled-expenses.pay');
            Route::resource('scheduled-expenses', ScheduledExpenseController::class)->except(['show']);

            // Team management (Phase 6)
            Route::resource('team', TeamController::class, ['parameters' => ['team' => 'user']])
                ->only(['index', 'create', 'store', 'edit', 'update']);

            // Activity Log
            Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
        });
    });
});
