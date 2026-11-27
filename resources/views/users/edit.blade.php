@extends('layouts.panel')

@section('title', 'Ubah Pengguna')
@section('heading', 'Ubah Pengguna')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}" novalidate>
            @csrf
            @method('PUT')
            @include('users._form')
        </form>
    </div>
</div>
@endsection
