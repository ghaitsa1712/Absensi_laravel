@extends('layouts.app')

@section('title', 'Absen Keluar')

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Absen Keluar</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('attendances.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Kotak Absen Masuk --}}
                <div class="border p-4 mb-3 rounded">
                    <h6 class="mb-3">🕒 Absen Masuk</h6>

                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ $attendance->date }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Jam Masuk</label>
                        <input type="time" name="time_in" class="form-control" value="{{ $attendance->time_in }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Lokasi Masuk</label>
                        <input type="text" name="latlon_in" class="form-control" value="{{ $attendance->latlon_in }}" readonly>
                    </div>
                </div>

                {{-- Kotak Absen Keluar --}}
                <div class="border p-4 rounded">
                    <h6 class="mb-3">🕔 Absen Keluar</h6>

                    <div class="form-group">
                        <label>Jam Keluar</label>
                        <input type="time" name="time_out" id="time_out" class="form-control" 
                            value="{{ $attendance->time_out }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Lokasi Keluar</label>
                        <input type="text" name="latlon_out" id="latlon_out" class="form-control" 
                            value="{{ $attendance->latlon_out }}" readonly>
                        <small class="form-text text-muted" id="lokasi-status">
                            {{ $attendance->time_out ? '✅ Lokasi sudah dicatat.' : '📍 Mengambil lokasi...' }}
                        </small>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="mt-4">
                    @if ($attendance->time_out)
                        <button type="button" class="btn btn-secondary" disabled>
                            🔒 Sudah absen keluar hari ini
                        </button>
                    @else
                        <button type="submit" class="btn btn-success">
                            Absen Keluar
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
@if (!$attendance->time_out)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const lokasiField = document.getElementById('latlon_out');
                const timeField = document.getElementById('time_out');

                lokasiField.value = `${lat},${lon}`;
                lokasiField.readOnly = true;
                lokasiField.setAttribute('readonly', 'readonly');

                document.getElementById('lokasi-status').innerText = `✅ Lokasi didapatkan: ${lat}, ${lon}`;

                const now = new Date();
                const jam = now.getHours().toString().padStart(2, '0');
                const menit = now.getMinutes().toString().padStart(2, '0');
                timeField.value = `${jam}:${menit}`;
                timeField.readOnly = true;
                timeField.setAttribute('readonly', 'readonly');
            }, function () {
                document.getElementById('lokasi-status').innerText = '❌ Gagal mendapatkan lokasi.';
            });
        } else {
            document.getElementById('lokasi-status').innerText = '❌ Browser tidak mendukung geolokasi.';
        }
    });
</script>
@endif
@endpush
