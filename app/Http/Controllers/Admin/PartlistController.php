<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partlist;
use Illuminate\Http\Request;

class PartlistController extends Controller
{
    public function index()
    {
        $partlists = Partlist::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.partlists.index', compact('partlists'));
    }

    public function create()
    {
        return view('admin.partlists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'drawing_no_supplier' => 'required|string|max:255|unique:partlists,drawing_no_supplier',
            'name' => 'required|string|max:255',
            'size_type' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
            'sub_assy_drawing_unit' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        Partlist::create($validated);

        return redirect()
            ->route('admin.partlists.index')
            ->with('success', 'Partlist created successfully.');
    }

    public function show(Partlist $partlist)
    {
        return view('admin.partlists.show', compact('partlist'));
    }

    public function edit(Partlist $partlist)
    {
        return view('admin.partlists.edit', compact('partlist'));
    }

    public function update(Request $request, Partlist $partlist)
    {
        $validated = $request->validate([
            'drawing_no_supplier' => 'required|string|max:255|unique:partlists,drawing_no_supplier,' . $partlist->id,
            'name' => 'required|string|max:255',
            'size_type' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
            'sub_assy_drawing_unit' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        $partlist->update($validated);

        return redirect()
            ->route('admin.partlists.index')
            ->with('success', 'Partlist updated successfully.');
    }

    public function destroy(Partlist $partlist)
    {
        if ($partlist->tickets()->exists()) {
            return back()->with('error', 'Partlist sudah dipakai ticket, tidak bisa dihapus.');
        }

        $partlist->delete();

        return redirect()
            ->route('admin.partlists.index')
            ->with('success', 'Partlist deleted successfully.');
    }
}