@extends('layouts.public')

@section('title', 'Akses Ditolak')

@section('content')
    <div class="text-center py-5">
        <h1 class="display-1 fw-bold text-danger">403</h1>
        <p class="lead">{{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses ke halaman ini.' }}</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
@endsection
