<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Tampilkan daftar user (khusus admin)
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Khusus admin');
        }

        $users = User::where('name', 'like', '%' . request('name') . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('pages.users.index', compact('users'));
    }

    // Tampilkan form tambah user
    public function create()
    {
        return view('pages.users.create');
    }

    // Simpan user baru
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'role'       => $request->role,
            'position'   => $request->position,
            'department' => $request->department,
            'password'   => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    // Tampilkan form edit user
    public function edit(User $user)
    {
        return view('pages.users.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'role'       => $request->role,
            'position'   => $request->position,
            'department' => $request->department,
        ]);

        // Jika password diisi, update juga password
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }

    // Hapus user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
