<?php

namespace App\Http\Controllers\Approver;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Stockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketApprovalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Pending Tickets
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $tickets = Ticket::with([
            'creator',
            'approver',
            'partlist',
        ])
            ->where('approver_id', request()->user()->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('approver.tickets.index', compact('tickets'));
    }

    /*
    |--------------------------------------------------------------------------
    | Approval History
    |--------------------------------------------------------------------------
    */

    public function history()
    {
        $tickets = Ticket::with([
            'creator',
            'approver',
            'partlist',
        ])
            ->where('approver_id', request()->user()->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->get();

        return view('approver.history', compact('tickets'));
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE TICKET (Production Ready)
    |--------------------------------------------------------------------------
    */

    public function submitApprove(Request $request, Ticket $ticket)
    {
        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        if ($ticket->approver_id !== $request->user()->id) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent double approval
        |--------------------------------------------------------------------------
        */

        if ($ticket->status !== 'pending') {
            return redirect()
                ->route('approver.tickets.index')
                ->with('error', 'Ticket already processed.');
        }

        try {

            DB::transaction(function () use ($ticket) {

                /*
                |--------------------------------------------------------------------------
                | Lock ticket row
                |--------------------------------------------------------------------------
                */

                $ticket = Ticket::query()
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                /*
                |--------------------------------------------------------------------------
                | Re-check status after lock
                |--------------------------------------------------------------------------
                */

                if ($ticket->status !== 'pending') {
                    throw new \Exception('Ticket already processed.');
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Partlist
                |--------------------------------------------------------------------------
                */

                if (!$ticket->partlist_id) {
                    throw new \Exception('Partlist not found.');
                }

                $partlist = $ticket->partlist()
                    ->lockForUpdate()
                    ->first();

                if (!$partlist) {
                    throw new \Exception('Partlist not found.');
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Qty
                |--------------------------------------------------------------------------
                */

                if ($ticket->qty <= 0) {
                    throw new \Exception('Invalid quantity.');
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Stock
                |--------------------------------------------------------------------------
                */

                if ($partlist->qty < $ticket->qty) {
                    throw new \Exception(
                        'Stock not enough. Available stock: ' . $partlist->qty
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Reduce Stock
                |--------------------------------------------------------------------------
                */

                $partlist->decrement('qty', $ticket->qty);

                /*
                |--------------------------------------------------------------------------
                | Approve Ticket
                |--------------------------------------------------------------------------
                */

                $ticket->update([
                    'status' => 'approved',
                    'approved_at' => now(),

                    'rejected_at' => null,
                    'rejection_remark' => null,

                    'approval_remark' => 'Approved by approver',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Create Stockout History
                |--------------------------------------------------------------------------
                */

                Stockout::create([
                    'ticket_id' => $ticket->id,
                    'qty' => $ticket->qty,
                ]);
            });

            return redirect()
                ->route('approver.tickets.index')
                ->with('success', 'Ticket approved successfully.');
        } catch (\Throwable $e) {

            return redirect()
                ->route('approver.tickets.index')
                ->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reject Page
    |--------------------------------------------------------------------------
    */

    public function reject(Ticket $ticket)
    {
        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        if ($ticket->approver_id !== request()->user()->id) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent re-process
        |--------------------------------------------------------------------------
        */

        if ($ticket->status !== 'pending') {

            return redirect()
                ->route('approver.tickets.index')
                ->with('error', 'Ticket already processed.');
        }

        return view('approver.tickets.reject', compact('ticket'));
    }

    /*
    |--------------------------------------------------------------------------
    | Submit Reject
    |--------------------------------------------------------------------------
    */

    public function submitReject(Request $request, Ticket $ticket)
    {
        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        if ($ticket->approver_id !== $request->user()->id) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent re-process
        |--------------------------------------------------------------------------
        */

        if ($ticket->status !== 'pending') {

            return redirect()
                ->route('approver.tickets.index')
                ->with('error', 'Ticket already processed.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'rejection_remark' => 'required|string|max:500',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Ticket
        |--------------------------------------------------------------------------
        */

        $ticket->update([
            'status' => 'rejected',

            'rejection_remark' => $validated['rejection_remark'],
            'rejected_at' => now(),

            'approved_at' => null,
            'approval_remark' => null,
        ]);

        return redirect()
            ->route('approver.tickets.index')
            ->with('success', 'Ticket rejected successfully.');
    }
}
