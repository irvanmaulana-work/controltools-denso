<?php

namespace App\Http\Controllers\Approver;

use App\Http\Controllers\Controller;
use App\Models\Partlist;
use Illuminate\Http\Request;

class PartlistApprovalController extends Controller
{
    public function index()
    {
        $partlists = Partlist::query()
            ->orderByDesc('id')
            ->get();

        return view('approver.add_partlist.index', compact('partlists'));
    }

    public function create()
    {
        return view('approver.add_partlist.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'drawing_no_supplier' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'size_type' => 'required|string|max:255',
            'qty' => 'required|integer|in:1',
            'sub_assy_drawing_unit' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        Partlist::create($validated);

        return redirect()
            ->route('approver.add_partlist.index')
            ->with('success', 'Partlist added successfully.');
    }
}