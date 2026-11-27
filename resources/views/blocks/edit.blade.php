@extends('layouts.panel')

@section('title', 'Ubah Blok')
@section('heading', 'Ubah Nama Blok')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('blocks.update', $block) }}" novalidate>
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Kode Blok</label>
                <input type="text" class="form-control" value="{{ $block->code }}" disabled>
                <div class="form-text">Kode blok tidak dapat diubah karena menjadi bagian dari denah.</div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Nama Blok</label>
                <input type="text" id="name" name="name" value="{{ old('name', $block->name) }}"
                       class="form-control @error('name') is-invalid @enderror" required autofocus>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Simpan
                </button>
                <a href="{{ route('blocks.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
