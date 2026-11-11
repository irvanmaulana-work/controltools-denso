<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AuthController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PartlistController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\Member\TicketController;
use App\Http\Controllers\Member\StockoutController;

use App\Http\Controllers\Approver\TicketApprovalController;
use App\Http\Controllers\Approver\PartlistApprovalController;

use App\Models\Ticket;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [AuthController::class, 'loginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Redirect Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get('/dashboard', function () {

    $role = auth()->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'approver' => redirect()->route('approver.dashboard'),
        default => redirect()->route('member.dashboard'),
    };
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'index'])
            ->name('dashboard');

        Route::resource('partlists', PartlistController::class);

        Route::resource('users', UserController::class);
    });

/*
|--------------------------------------------------------------------------
| Approver Routes
|--------------------------------------------------------------------------
*/

Route::prefix('approver')
    ->name('approver.')
    ->middleware(['auth', 'role:approver'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', function () {

            $approverId = request()->user()->id;

            $baseQuery = Ticket::query()
                ->where('approver_id', $approverId);

            $pendingCount = (clone $baseQuery)
                ->where('status', 'pending')
                ->count();

            $approvedCount = (clone $baseQuery)
                ->where('status', 'approved')
                ->count();

            $rejectedCount = (clone $baseQuery)
                ->where('status', 'rejected')
                ->count();

            $pendingTickets = (clone $baseQuery)
                ->with(['creator', 'approver'])
                ->where('status', 'pending')
                ->latest()
                ->limit(10)
                ->get();

            return view('approver.dashboard', compact(
                'pendingCount',
                'approvedCount',
                'rejectedCount',
                'pendingTickets'
            ));
        })->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Ticket Approval
        |--------------------------------------------------------------------------
        */

        Route::get('/tickets', [TicketApprovalController::class, 'index'])
            ->name('tickets.index');

        Route::get('/tickets/history', [TicketApprovalController::class, 'history'])
            ->name('tickets.history');

        Route::post('/tickets/{ticket}/approve', [TicketApprovalController::class, 'submitApprove'])
            ->name('tickets.approve.submit');

        Route::get('/tickets/{ticket}/reject', [TicketApprovalController::class, 'reject'])
            ->name('tickets.reject');

        Route::post('/tickets/{ticket}/reject', [TicketApprovalController::class, 'submitReject'])
            ->name('tickets.reject.submit');

        /*
        |--------------------------------------------------------------------------
        | Add Partlist
        |--------------------------------------------------------------------------
        */

        Route::get('/add-partlist', [PartlistApprovalController::class, 'index'])
            ->name('add_partlist.index');

        Route::get('/add-partlist/create', [PartlistApprovalController::class, 'create'])
            ->name('add_partlist.create');

        Route::post('/add-partlist', [PartlistApprovalController::class, 'store'])
            ->name('add_partlist.store');
    });

/*
|--------------------------------------------------------------------------
| Member Routes
|--------------------------------------------------------------------------
*/

Route::prefix('member')
    ->name('member.')
    ->middleware(['auth', 'role:member'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', function () {

            $userId = auth()->id();

            $myTickets = Ticket::where('created_by', $userId)->count();

            $approved = Ticket::where('created_by', $userId)
                ->where('status', 'approved')
                ->count();

            $pending = Ticket::where('created_by', $userId)
                ->where('status', 'pending')
                ->count();

            $rejected = Ticket::where('created_by', $userId)
                ->where('status', 'rejected')
                ->count();

            $latestTickets = Ticket::with('approver')
                ->where('created_by', $userId)
                ->latest()
                ->limit(5)
                ->get();

            return view('member.dashboard', compact(
                'myTickets',
                'approved',
                'pending',
                'rejected',
                'latestTickets'
            ));
        })->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Tickets
        |--------------------------------------------------------------------------
        */

        Route::get('/tickets/create', [TicketController::class, 'create'])
            ->name('tickets.create');

        Route::post('/tickets', [TicketController::class, 'store'])
            ->name('tickets.store');

        Route::get('/tickets', [TicketController::class, 'index'])
            ->name('tickets.index');

        /*
        |--------------------------------------------------------------------------
        | Stockout History
        |--------------------------------------------------------------------------
        */

        Route::get('/stockouts/history', [StockoutController::class, 'history'])
            ->name('stockouts.history');
    });