@extends('layouts.app')

@section('title', 'Attendances')

@push('style')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.1) !important;
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050;
        pointer-events: auto !important;
    }

    body.modal-open {
        overflow: auto !important;
        padding-right: 0 !important;
    }

    .modal-content {
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .modal-backdrop.show {
        opacity: 0.2 !important;
    }

    .alert-fixed {
        position: relative;
        margin-top: 20px;
        z-index: 9999;
    }

    .table-responsive {
        overflow-x: auto;
        white-space: nowrap;
    }

    .table td,
    .table th {
        vertical-align: middle;
        white-space: nowrap;
    }

    .date-separator {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: left;
    }
</style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">

        {{-- ALERT --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-fixed" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show alert-fixed" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="section-header mt-3">
            <h1>Absensi</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Absensi</div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>All Absensi</h4>
                    </div>
                    <div class="card-body">

                        {{-- Tombol Absen hanya di halaman pertama --}}
                        @if (request()->get('page', 1) == 1)
                        <div class="d-flex justify-content-between flex-wrap mb-3">
                            <div>
                                @php
                                    $sudahAbsenMasuk = $attendances->where('date', now()->toDateString())->where('user_id', auth()->id())->isNotEmpty();
                                    $absenHariIni = $attendances->where('date', now()->toDateString())->where('user_id', auth()->id())->first();
                                    $sudahAbsenKeluar = $absenHariIni && $absenHariIni->time_out !== null;
                                @endphp

                                @if (!$sudahAbsenMasuk)
                                    <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#absenModal" data-backdrop="false">
                                        <i class="fas fa-sign-in-alt mr-1"></i> Absen Masuk
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-secondary mr-2" disabled>
                                        Sudah absen hari ini
                                    </button>
                                @endif

                                @if ($sudahAbsenMasuk && !$sudahAbsenKeluar)
                                    <a href="{{ route('attendances.edit', $absenHariIni->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-sign-out-alt mr-1"></i> Absen Keluar
                                    </a>
                                @elseif ($sudahAbsenMasuk && $sudahAbsenKeluar)
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        Sudah absen keluar hari ini
                                    </button>
                                @endif
                                <a href="{{ route('rekap.create') }}" class="btn btn-sm btn-warning">
    <i class="fas fa-envelope-open-text mr-1"></i> Ajukan Izin
</a>

                            </div>

                            {{-- Search --}}
                            <div>
                                <form method="GET" action="{{ route('attendances.index') }}">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="name" placeholder="Search by name">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif

                        {{-- Tabel Attendance --}}
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Nama Sekolah</th>
                                        <th>Jurusan</th>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Latlong In</th>
                                        <th>Latlong Out</th>
                                        @if (auth()->user()->role === 'admin')
                                            <th>Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
    @foreach ($attendances as $attendance)
        <tr>
            <td>{{ $attendance->user->name }}</td>
            <td>{{ $attendance->school_name }}</td>
            <td>{{ $attendance->jurusan }}</td>
            <td>{{ $attendance->date }}</td>
            <td>{{ $attendance->time_in }}</td>
            <td>{{ $attendance->time_out ?? '-' }}</td>
            <td>{{ $attendance->latlon_in }}</td>
            <td>{{ $attendance->latlon_out ?? '-' }}</td>
            @if (auth()->user()->role === 'admin')
                <td>
                    <form action="{{ route('attendances.destroy', $attendance->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </td>
            @endif
        </tr>
    @endforeach
</tbody>

                            </table>
                        </div>

                        <div class="float-right">
                            {{ $attendances->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Absen Masuk --}}
        <div class="modal fade" id="absenModal" tabindex="-1" role="dialog" aria-labelledby="absenModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="{{ route('attendances.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Form Absen</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="border p-3 mb-3 rounded">
                                <div class="form-group">
                                <label for="school_name">Nama Sekolah</label>
                                <input type="text" class="form-control" name="school_name" id="school_name" required>
                            </div>
                            <div class="form-group">
                                <label for="jurusan">Jurusan</label>
                                <input type="text" class="form-control" name="jurusan" id="jurusan" required>
                            </div>
                                <h6 class="mb-3">🕒 Absen Masuk</h6>
                                
                                <div class="form-group">
                                    <label for="date">Tanggal</label>
                                    <input type="date" class="form-control" name="date" id="date" required readonly>
                                </div>
                                <div class="form-group">
                                    <label for="time_in">Jam Masuk</label>
                                    <input type="time" class="form-control" name="time_in" id="time_in" required readonly>
                                </div>
                                <div class="form-group">
                                    <label for="latlon_in">Latlong Masuk</label>
                                    <input type="text" class="form-control" name="latlon_in" id="latlon_in" required readonly>
                                </div>
                            </div>
                            <div class="border p-3 rounded">
                                <h6 class="mb-3">🕔 Absen Keluar</h6>
                                <div class="form-group">
                                    <label for="time_out">Jam Keluar</label>
                                    <input type="time" class="form-control" name="time_out" id="time_out" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="latlon_out">Latlong Keluar</label>
                                    <input type="text" class="form-control" name="latlon_out" id="latlon_out" disabled>
                                </div>
                            </div>
                            <div id="lokasi-status" class="text-muted mt-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Absen</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#absenModal').on('show.bs.modal', function () {
            const now = new Date();
            document.getElementById('date').value = now.toISOString().substring(0, 10);
            document.getElementById('time_in').value = now.toTimeString().substring(0, 5);
            document.getElementById('time_out').value = '';
            document.getElementById('latlon_out').value = '';
            document.getElementById('time_out').disabled = true;
            document.getElementById('latlon_out').disabled = true;
            document.getElementById('lokasi-status').innerText = '⏳ Absen keluar hanya bisa diisi nanti melalui halaman Edit.';
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    document.getElementById('latlon_in').value = `${lat},${lon}`;
                    document.getElementById('lokasi-status').innerText += ` 📍 Lokasi: ${lat}, ${lon}`;
                }, function () {
                    document.getElementById('lokasi-status').innerText = '❌ Gagal mendapatkan lokasi.';
                });
            } else {
                document.getElementById('lokasi-status').innerText = 'Browser tidak mendukung geolokasi.';
            }
        });
    });
</script>
@endpush
