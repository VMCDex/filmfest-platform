<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\ApplicationController;
use App\Http\Controllers\Organizer\ReportController;
use App\Http\Controllers\Participant\FilmController;
use App\Http\Controllers\Jury\JuryController;
use App\Http\Controllers\Visitor\VisitorController;

// Главная → регистрация
Route::get('/', function () { return redirect('/register'); });

// Аутентификация
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('/login');
})->name('logout');

// Личный кабинет (редирект по роли)
Route::get('/dashboard', function () {
    if (!auth()->check()) return redirect('/login');
    $role = auth()->user()->role;
    if ($role === 'admin') return redirect('/admin/users');
    if ($role === 'organizer') return redirect('/organizer/events');
    if ($role === 'participant') return redirect('/participant/films');
    if ($role === 'jury') return redirect('/jury/films');
    if ($role === 'visitor') return redirect('/visitor');
    return redirect('/');
})->name('dashboard');

// === АДМИН-ПАНЕЛЬ ===
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    Route::put('/users/{user}/status', [UserController::class, 'toggleStatus']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

// === ПАНЕЛЬ ОРГАНИЗАТОРА ===
Route::middleware(['auth', 'role:organizer,admin'])->prefix('organizer')->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('organizer.events');
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{event}/edit', [EventController::class, 'edit']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::put('/events/{event}/archive', [EventController::class, 'archive']);
    Route::put('/events/{event}/publish', [EventController::class, 'publish']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    Route::get('/applications', [ApplicationController::class, 'index'])->name('organizer.applications');
    Route::put('/applications/{application}/review', [ApplicationController::class, 'review']);

    Route::get('/reports', [ReportController::class, 'index'])->name('organizer.reports');
    Route::get('/reports/export/xlsx', [ReportController::class, 'exportXlsx']);
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf']);
});

// === ПАНЕЛЬ УЧАСТНИКА ===
Route::middleware(['auth', 'role:participant'])->prefix('participant')->group(function () {
    Route::get('/films', [FilmController::class, 'index'])->name('participant.films');
    Route::post('/films', [FilmController::class, 'store']);
    Route::get('/applications', [FilmController::class, 'applications'])->name('participant.applications');
    Route::post('/applications/submit', [FilmController::class, 'submitApplication']);
});

// === ПАНЕЛЬ ЖЮРИ ===
Route::middleware(['auth', 'role:jury,admin'])->prefix('jury')->group(function () {
    Route::get('/films', [JuryController::class, 'index'])->name('jury.films');
    Route::post('/films/score', [JuryController::class, 'store']);
});

// === ПАНЕЛЬ ЗРИТЕЛЯ ===
Route::middleware(['auth', 'role:visitor'])->prefix('visitor')->group(function () {
    Route::get('/', [VisitorController::class, 'index'])->name('visitor.index');
    Route::post('/tickets/{event}', [VisitorController::class, 'buyTicket']);
    Route::post('/votes/{film}', [VisitorController::class, 'vote']);
    Route::post('/reviews/{film}', [VisitorController::class, 'submitReview']);
});