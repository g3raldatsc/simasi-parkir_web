@extends('layouts.panel')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="h5">Selamat datang, {{ auth()->user()->name }}</h2>
            <p class="mb-0 text-body-secondary">
                Anda masuk sebagai <strong>{{ ucfirst(auth()->user()->role) }}</strong>.
            </p>
        </div>
    </div>
@endsection
