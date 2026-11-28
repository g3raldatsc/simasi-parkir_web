@extends('layouts.panel')

@section('title', 'Ubah Kendaraan')
@section('heading', 'Ubah Kendaraan')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" novalidate>
            @csrf
            @method('PUT')
            @include('vehicles._form')
        </form>
    </div>
</div>
@endsection
