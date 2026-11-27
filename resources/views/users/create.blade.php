@extends('layouts.panel')

@section('title', 'Tambah Pengguna')
@section('heading', 'Tambah Pengguna')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}" novalidate>
            @csrf
            @include('users._form')
        </form>
    </div>
</div>
@endsection
