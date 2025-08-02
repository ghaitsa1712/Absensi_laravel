<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user')->orderBy('id', 'desc');

        // Jika bukan admin, hanya tampilkan data user yang sedang login
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        // Fitur search hanya untuk admin
        if (auth()->user()->role === 'admin' && $request->input('name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('name') . '%');
            });
        }

        $attendances = $query->paginate(10);

        return view('pages.absensi.index', compact('attendances'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'latlon_in' => 'required',
            'school_name' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
        ]);

        Attendance::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'time_in' => $request->time_in,
            'latlon_in' => $request->latlon_in,
            'school_name' => $request->school_name,
            'jurusan' => $request->jurusan,
        ]);

        return redirect()->back()->with('success', 'Absen berhasil disimpan!');
    }

    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Cegah user mengedit data milik orang lain (kecuali admin)
        if (auth()->user()->role !== 'admin' && $attendance->user_id !== auth()->id()) {
            abort(403);
        }

        return view('pages.absensi.edit', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'nullable',
            'latlon_in' => 'required|string',
            'latlon_out' => 'nullable|string',
        ]);

        $attendance = Attendance::findOrFail($id);

        // Cegah update data orang lain (kecuali admin)
        if (auth()->user()->role !== 'admin' && $attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $attendance->update([
            'date' => $request->date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'latlon_in' => $request->latlon_in,
            'latlon_out' => $request->latlon_out,
        ]);

        return redirect()->route('attendances.index')->with('success', 'Data absen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Cegah user hapus data orang lain
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $attendance->delete();

        return redirect()->route('attendances.index')->with('success', 'Absen berhasil dihapus.');
    }
}
