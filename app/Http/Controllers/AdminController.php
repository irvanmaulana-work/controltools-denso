<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Partlist;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();

        $totalPartlists = Partlist::count();

        $pendingTickets = Ticket::where('status', 'pending')->count();

        $approvedToday = Ticket::where('status', 'approved')
            ->whereDate('approved_at', today())
            ->count();

        $lowStocks = Partlist::where('qty', '<=', 1)
            ->orderBy('qty')
            ->limit(10)
            ->get();

        $latestTickets = Ticket::with(['creator', 'approver'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalPartlists',
            'pendingTickets',
            'approvedToday',
            'lowStocks',
            'latestTickets'
        ));
    }
}