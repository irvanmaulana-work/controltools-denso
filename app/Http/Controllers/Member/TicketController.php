<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Partlist;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function create()
    {
        $approvers = User::where('role', 'approver')->get();

        $partlists = Partlist::select([
            'id',
            'name',
            'drawing_no_supplier',
            'size_type',
            'sub_assy_drawing_unit',
            'location',
            'qty',
        ])
            ->orderBy('name')
            ->get();

        $partlistsJson = $partlists->map(function ($p) {
            return [
                'name' => $p->name,
                'drawing_no_supplier' => $p->drawing_no_supplier,
                'size_type' => $p->size_type,
                'sub_assy_drawing_unit' => $p->sub_assy_drawing_unit,
                'location' => $p->location,
                'qty' => $p->qty,
            ];
        });

        return view('member.tickets.create', compact(
            'approvers',
            'partlists',
            'partlistsJson'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'approver_id' => 'required|exists:users,id',
            'remark' => 'nullable|string|max:500',
            'line_requester' => 'required|string|max:255',
            'partlist_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
        ]);

        $approver = User::where('id', $validated['approver_id'])
            ->where('role', 'approver')
            ->first();

        if (!$approver) {
            return back()->withErrors([
                'approver_id' => 'Approver tidak valid'
            ])->withInput();
        }

        $partlist = Partlist::where('name', $validated['partlist_name'])->first();

        if (!$partlist) {
            return back()->withErrors([
                'partlist_name' => 'Partlist tidak ditemukan'
            ])->withInput();
        }

        if ($validated['qty'] > $partlist->qty) {

            return back()->withErrors([
                'qty' => 'Qty melebihi stock tersedia. Stock saat ini: ' . $partlist->qty
            ])->withInput();
        }

        DB::transaction(function () use ($request, $validated, $partlist) {

            Ticket::create([
                'title' => $validated['partlist_name'],
                'created_by' => $request->user()->id,
                'approver_id' => $validated['approver_id'],
                'remark' => $validated['remark'],
                'status' => 'pending',

                'requester' => $request->user()->name,
                'line_requester' => $validated['line_requester'],

                'partlist_name' => $validated['partlist_name'],
                'drawing_no_supplier' => $partlist->drawing_no_supplier,
                'size_type' => $partlist->size_type,
                'qty' => $validated['qty'],
                'sub_assy_drawing_unit' => $partlist->sub_assy_drawing_unit,
                'location' => $partlist->location,

                'partlist_id' => $partlist->id,
            ]);
        });

        return redirect()
            ->route('member.tickets.index')
            ->with('success', 'Ticket submitted for approval.');
    }

    public function index()
    {
        $tickets = Ticket::with(['approver'])
            ->where('created_by', request()->user()->id)
            ->orderByDesc('id')
            ->get();

        return view('member.tickets.index', compact('tickets'));
    }
}
