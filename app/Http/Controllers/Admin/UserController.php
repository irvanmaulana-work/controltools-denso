<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'npk' => 'required|string|max:255|unique:users,npk',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,approver,member',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'npk' => $validated['npk'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'npk' => 'required|string|max:255|unique:users,npk,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,approver,member',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'npk' => $validated['npk'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        // Jangan hapus histori produksi
        if ($user->createdTickets()->exists() || $user->approvals()->exists()) {
            return back()->with('error', 'User memiliki histori ticket, tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
