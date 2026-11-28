@extends('layouts.panel')

@section('title', 'Tambah Kendaraan')
@section('heading', 'Tambah Kendaraan')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('vehicles.store') }}" novalidate>
            @csrf
            @include('vehicles._form')
        </form>
    </div>
</div>
@endsection
