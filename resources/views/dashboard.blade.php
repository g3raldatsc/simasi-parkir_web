@extends('layouts.panel')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Selamat datang, {{ auth()->user()->name }}</h2>
        <p class="mb-0 text-body-secondary">
            Anda masuk sebagai <strong>{{ ucfirst(auth()->user()->role) }}</strong>.
        </p>
    </div>
</div>

<h2 class="h6 text-uppercase text-body-secondary mb-2">Ringkasan Parkir</h2>
@if ($parkir)
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Slot Aktif</div>
                <div class="fs-2 fw-semibold">{{ $parkir['total'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Terisi</div>
                <div class="fs-2 fw-semibold text-danger">{{ $parkir['terisi'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Kosong</div>
                <div class="fs-2 fw-semibold text-success">{{ $parkir['kosong'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Masuk Hari Ini</div>
                <div class="fs-2 fw-semibold">{{ $parkir['masuk_hari_ini'] }}</div>
            </div></div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between small mb-1">
                <span>Tingkat keterisian</span><span>{{ $parkir['persen'] }}%</span>
            </div>
            <div class="progress" role="progressbar" aria-valuenow="{{ $parkir['persen'] }}"
                 aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: {{ $parkir['persen'] }}%"></div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-secondary mb-4">
        Data parkir akan tampil di sini setelah modul slot dan transaksi parkir tersedia.
    </div>
@endif

@if ($pengguna)
    <h2 class="h6 text-uppercase text-body-secondary mb-2">Pengguna Sistem</h2>
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Total Akun</div>
                <div class="fs-2 fw-semibold">{{ $pengguna['total'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Akun Aktif</div>
                <div class="fs-2 fw-semibold text-success">{{ $pengguna['aktif'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Admin</div>
                <div class="fs-2 fw-semibold">{{ $pengguna['admin'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-body-secondary small">Petugas</div>
                <div class="fs-2 fw-semibold">{{ $pengguna['petugas'] }}</div>
            </div></div>
        </div>
    </div>
@endif
@endsection
