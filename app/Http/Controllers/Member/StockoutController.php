<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Stockout;

class StockoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Stockout History
    |--------------------------------------------------------------------------
    */

    public function history()
    {
        $userId = request()->user()->id;

        /*
        |--------------------------------------------------------------------------
        | Ambil hanya stockout milik user login
        |--------------------------------------------------------------------------
        */

        $stockouts = Stockout::query()

            ->with([
                'ticket.creator',
                'ticket.approver',
                'ticket.partlist',
            ])

            ->whereHas('ticket', function ($query) use ($userId) {

                $query
                    ->where('created_by', $userId)
                    ->where('status', 'approved');
            })

            ->latest()

            ->paginate(10);

        return view('member.stockouts.history', compact('stockouts'));
    }
}
