<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\MonthClosingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatGroupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OwesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\ScheduledExpenseController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TwoFactorController;
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

    // 2FA challenge — auth only (no verified required, intercepted before dashboard)
    Route::get('/2fa/challenge',  [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [TwoFactorController::class, 'challenge'])->name('2fa.verify');

    // Email verification
    Route::get('/verify-email', [VerifyEmailController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // All verified-user routes (also protected by TwoFactorMiddleware)
    Route::middleware(['verified', 'two-factor'])->group(function () {
        Route::get('/', fn () => redirect()->route('dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile (all users)
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::get('/profile/login-history', [LoginHistoryController::class, 'index'])->name('profile.login-history');

        // 2FA management (setup, confirm, disable, recovery codes) — all verified users
        Route::get('/2fa/setup',           [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
        Route::post('/2fa/confirm',        [TwoFactorController::class, 'confirmSetup'])->name('2fa.confirm');
        Route::get('/2fa/recovery-codes',  [TwoFactorController::class, 'recoveryCodes'])->name('2fa.recovery-codes');
        Route::post('/2fa/disable',        [TwoFactorController::class, 'disable'])->name('2fa.disable');

        // Clients — all users
        Route::resource('clients', ClientController::class);

        // Reimbursements — all users (static segments before {reimbursement} wildcard)
        Route::get('/reimbursements/create',             [ReimbursementController::class, 'create'])->name('reimbursements.create');
        Route::post('/reimbursements',                   [ReimbursementController::class, 'store'])->name('reimbursements.store');
        Route::get('/reimbursements',                    [ReimbursementController::class, 'index'])->name('reimbursements.index');
        Route::get('/reimbursements/{reimbursement}',      [ReimbursementController::class, 'show'])->name('reimbursements.show');
        Route::get('/reimbursements/{reimbursement}/edit', [ReimbursementController::class, 'edit'])->name('reimbursements.edit');
        Route::put('/reimbursements/{reimbursement}',      [ReimbursementController::class, 'update'])->name('reimbursements.update');
        Route::delete('/reimbursements/{reimbursement}',   [ReimbursementController::class, 'destroy'])->name('reimbursements.destroy');

        // Invoices — all users
        Route::resource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/send',                        [InvoiceController::class, 'sendEmail'])->name('invoices.send');
        Route::post('/invoices/{invoice}/mark-paid',                   [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::post('/invoices/{invoice}/mark-unpaid',                 [InvoiceController::class, 'markUnpaid'])->name('invoices.mark-unpaid');
        Route::get('/invoices/{invoice}/pdf',                          [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/payments',                    [InvoicePaymentController::class, 'store'])->name('invoice-payments.store');
        Route::delete('/invoices/{invoice}/payments/{payment}',        [InvoicePaymentController::class, 'destroy'])->name('invoice-payments.destroy');

        // Calendar — all users
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
        Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
        Route::patch('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
        Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

        // Projects — static segments must come before {project} wildcard
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::middleware('role:manager')->group(function () {
            Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
            Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        });
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::patch('/projects/{project}/progress', [ProjectController::class, 'updateProgress'])->name('projects.progress');
        Route::middleware('role:manager')->group(function () {
            Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
            Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
            Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        });

        // Leads — manager+ only; static segments before {lead} wildcard
        Route::middleware('role:manager')->group(function () {
            Route::get('/leads/import',         [LeadImportController::class, 'show'])->name('leads.import.show');
            Route::post('/leads/import/preview',[LeadImportController::class, 'preview'])->name('leads.import.preview');
            Route::post('/leads/import',        [LeadImportController::class, 'import'])->name('leads.import.store');
            Route::get('/leads/create',         [LeadController::class, 'create'])->name('leads.create');
            Route::post('/leads',               [LeadController::class, 'store'])->name('leads.store');
            Route::get('/leads/{lead}/edit',    [LeadController::class, 'edit'])->name('leads.edit');
            Route::put('/leads/{lead}',         [LeadController::class, 'update'])->name('leads.update');
            Route::delete('/leads/{lead}',      [LeadController::class, 'destroy'])->name('leads.destroy');
            Route::patch('/leads/{lead}/stage',   [LeadController::class, 'updateStage'])->name('leads.stage');
            Route::post('/leads/{lead}/convert',  [LeadController::class, 'convertToProject'])->name('leads.convert');
            Route::get('/leads',                [LeadController::class, 'index'])->name('leads.index');
            Route::get('/leads/{lead}',         [LeadController::class, 'show'])->name('leads.show');

            // Client import
            Route::get('/clients/import',          [ClientImportController::class, 'show'])->name('clients.import.show');
            Route::post('/clients/import/preview', [ClientImportController::class, 'preview'])->name('clients.import.preview');
            Route::post('/clients/import',         [ClientImportController::class, 'import'])->name('clients.import.store');
        });

        // Notifications — all verified users; static 'count' and 'read-all' before {notification} wildcard
        Route::get('/notifications',             [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/count',       [NotificationController::class, 'count'])->name('notifications.count');
        Route::post('/notifications/read-all',   [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        // Chat — all verified users; static segments before {message} wildcard
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
        Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:30,1')->name('chat.store');
        Route::post('/chat/typing', [ChatController::class, 'typing'])->name('chat.typing');
        Route::get('/chat/attachments/{message}', [ChatController::class, 'attachment'])->name('chat.attachment');
        Route::post('/chat/messages/{message}/react', [ChatController::class, 'react'])->name('chat.react');

        // Chat groups — create/store manager+; show/members all auth (gated in controller)
        Route::middleware('role:manager')->group(function () {
            Route::post('/chat/groups', [ChatGroupController::class, 'store'])->name('chat-groups.store');
            Route::post('/chat/groups/{group}/members', [ChatGroupController::class, 'addMember'])->name('chat-groups.members.add');
            Route::delete('/chat/groups/{group}/members/{user}', [ChatGroupController::class, 'removeMember'])->name('chat-groups.members.remove');
        });
        Route::get('/chat/groups/{group}', [ChatGroupController::class, 'show'])->name('chat-groups.show');

        // Reports — manager+ only
        Route::middleware('role:manager')->group(function () {
            Route::get('/reports',          [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/revenue',  [ReportController::class, 'revenue'])->name('reports.revenue');
            Route::get('/reports/leads',    [ReportController::class, 'leads'])->name('reports.leads');
            Route::get('/reports/projects', [ReportController::class, 'projects'])->name('reports.projects');
            Route::get('/reports/invoices', [ReportController::class, 'invoices'])->name('reports.invoices');
        });

        // Manager-only routes
        Route::middleware('role:manager')->group(function () {
            // Reimbursement approval actions (manager only)
            Route::post('/reimbursements/{reimbursement}/approve',   [ReimbursementController::class, 'approve'])->name('reimbursements.approve');
            Route::post('/reimbursements/{reimbursement}/reject',    [ReimbursementController::class, 'reject'])->name('reimbursements.reject');
            Route::post('/reimbursements/{reimbursement}/reimburse', [ReimbursementController::class, 'reimburse'])->name('reimbursements.reimburse');

            // Who Owes Whom overview
            Route::get('/owes-overview', [OwesController::class, 'index'])->name('owes.index');

            // Company settings (opening balance)
            Route::get('/settings/company',  [CompanySettingController::class, 'edit'])->name('settings.company');
            Route::put('/settings/company',  [CompanySettingController::class, 'update'])->name('settings.company.update');

            // Month closing
            Route::get('/month-closings',  [MonthClosingController::class, 'index'])->name('month-closings.index');
            Route::post('/month-closings', [MonthClosingController::class, 'store'])->name('month-closings.store');

            // Loans — static segments before {loan} wildcard
            Route::get('/loans/create',  [LoanController::class, 'create'])->name('loans.create');
            Route::post('/loans',        [LoanController::class, 'store'])->name('loans.store');
            Route::get('/loans',         [LoanController::class, 'index'])->name('loans.index');
            Route::get('/loans/{loan}',              [LoanController::class, 'show'])->name('loans.show');
            Route::get('/loans/{loan}/edit',         [LoanController::class, 'edit'])->name('loans.edit');
            Route::put('/loans/{loan}',              [LoanController::class, 'update'])->name('loans.update');
            Route::delete('/loans/{loan}',           [LoanController::class, 'destroy'])->name('loans.destroy');
            Route::post('/loans/{loan}/repayments',              [LoanController::class, 'recordRepayment'])->name('loans.repayments.store');
            Route::delete('/loans/{loan}/repayments/{repayment}',[LoanController::class, 'deleteRepayment'])->name('loans.repayments.destroy');

            // Finances (Phase 4)
            Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
            Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
            Route::post('/transactions/{transaction}/reject',  [TransactionController::class, 'reject'])->name('transactions.reject');
            Route::resource('transactions', TransactionController::class)->except(['show']);
            Route::post('/scheduled-expenses/{scheduledExpense}/pay', [ScheduledExpenseController::class, 'pay'])->name('scheduled-expenses.pay');
            Route::resource('scheduled-expenses', ScheduledExpenseController::class)->except(['show']);

            // Team management (Phase 6)
            Route::post('/team/{user}/invite-link', [TeamController::class, 'inviteLink'])->name('team.invite-link');
            Route::resource('team', TeamController::class, ['parameters' => ['team' => 'user']])
                ->only(['index', 'create', 'store', 'edit', 'update']);

            // Activity Log
            Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
        });
    });
});
