<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Izin;

class RekapAbsenController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Khusus admin');
        }

        $tanggal_mulai = $request->input('tanggal_mulai');
        $tanggal_selesai = $request->input('tanggal_selesai');

        $query = Attendance::with('user');

        if ($tanggal_mulai && $tanggal_selesai) {
            $query->whereBetween('date', [$tanggal_mulai, $tanggal_selesai]);
        }

        $data = $query->orderBy('date', 'desc')->get();

        // Tambahkan status_terhitung otomatis
        $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0];

        foreach ($data as $item) {
            $status = $item->status;

            if (!$status) {
                if ($item->time_in) {
                    $jam_masuk = Carbon::parse($item->time_in);
                    $status = $jam_masuk->gt(Carbon::createFromTime(8, 0, 0)) ? 'Terlambat' : 'Hadir';
                } else {
                    $status = '-';
                }
            }

            $item->status_terhitung = $status;

            if ($status === 'Hadir') $summary['hadir']++;
            elseif ($status === 'Terlambat') $summary['terlambat']++;
            elseif ($status === 'Izin') $summary['izin']++;
        }

        return view('rekap.index', compact('data', 'summary', 'tanggal_mulai', 'tanggal_selesai'));
    }

    public function tampil(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggal_mulai = $request->tanggal_mulai;
        $tanggal_selesai = $request->tanggal_selesai;

        $query = Attendance::whereBetween('date', [$tanggal_mulai, $tanggal_selesai]);

        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        } elseif ($request->nama) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%');
            });
        }

        $data = $query->with('user')->get();

        $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0];

        foreach ($data as $item) {
            $status = $item->status;

            if (!$status) {
                if ($item->time_in) {
                    $jam_masuk = Carbon::parse($item->time_in);
                    $status = $jam_masuk->gt(Carbon::createFromTime(8, 0, 0)) ? 'Terlambat' : 'Hadir';
                } else {
                    $status = '-';
                }
            }

            $item->status_terhitung = $status;

            if ($status === 'Hadir') $summary['hadir']++;
            elseif ($status === 'Terlambat') $summary['terlambat']++;
            elseif ($status === 'Izin') $summary['izin']++;
        }

        return view('rekap.index', [
            'data' => $data,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'nama_input' => $request->nama ?? '',
            'summary' => $summary,
        ]);
    }

    public function izinIndex(Request $request)
{
    $query = Izin::with('user');

    if ($request->has('search')) {
        $search = $request->search;
        $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        });
    }

    $izinList = $query->latest()->get();

    // Cek apakah user sudah izin hari ini
    $userId = auth()->id();
    $today = now()->toDateString(); // atau date('Y-m-d')
    $sudahIzinHariIni = Izin::where('user_id', $userId)
        ->where('tanggal', $today)
        ->exists();

    return view('rekap.izin', compact('izinList', 'sudahIzinHariIni'));
}


    public function izinStore(Request $request)
{
    $userId = auth()->id();
    $today = date('Y-m-d');

    // Cek apakah sudah izin hari ini
    $sudahIzin = Izin::where('user_id', $userId)
        ->where('tanggal', $today)
        ->exists();

    // Kalau sudah izin, langsung ke halaman awal (tanpa simpan ulang)
    if ($sudahIzin) {
        return redirect()->route('home')->with('error', 'Kamu sudah mengajukan izin untuk hari ini.');
    }

    // Validasi input
    $request->validate([
        'alasan' => 'required|string',
    ]);

    // Simpan izin
    Izin::create([
        'user_id' => $userId,
        'tanggal' => $today,
        'alasan' => $request->alasan,
    ]);

    // Redirect ke halaman awal
    return redirect()->route('attendances.index')->with('success', 'Izin berhasil diajukan.');
}



    // Tampilkan detail izin
    public function izinShow($id)
    {
        $izin = Izin::with('user')->findOrFail($id);
        return view('rekap.izin_detail', compact('izin'));
    }

    // Form pengajuan izin
    public function izinCreate()
    {
        return view('rekap.create');
    }
}
