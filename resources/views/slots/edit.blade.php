@extends('layouts.panel')

@section('title', 'Ubah Slot')
@section('heading', 'Ubah Slot ' . $slot->label)

@section('content')
@php
    $terisi = $slot->relationLoaded('activeTransaction') && $slot->activeTransaction;
@endphp

<div class="card">
    <div class="card-body">
        <dl class="row mb-4">
            <dt class="col-sm-3">Slot</dt>
            <dd class="col-sm-9">{{ $slot->label }}</dd>
            <dt class="col-sm-3">Blok</dt>
            <dd class="col-sm-9">{{ $slot->block->code }} - {{ $slot->block->name }}</dd>
            <dt class="col-sm-3">Koordinat</dt>
            <dd class="col-sm-9 mb-0">({{ $slot->mapCell->x + 1 }}, {{ $slot->mapCell->y + 1 }})</dd>
        </dl>

        @if ($terisi)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Slot ini sedang terisi kendaraan, sehingga tidak dapat dinonaktifkan.
            </div>
        @endif

        <form method="POST" action="{{ route('slots.update', $slot) }}" novalidate>
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label d-block">Status Slot</label>
                @if ($terisi)
                    <input type="hidden" name="is_active" value="1">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked disabled>
                        <label class="form-check-label">Aktif</label>
                    </div>
                @else
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch">
                        <input class="form-check-input @error('is_active') is-invalid @enderror"
                               type="checkbox" id="is_active" name="is_active" value="1"
                               @checked(old('is_active', $slot->is_active))>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    @error('is_active') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="form-text">Slot nonaktif tidak akan direkomendasikan kepada pengendara.</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Catatan</label>
                <textarea id="notes" name="notes" rows="3"
                          class="form-control @error('notes') is-invalid @enderror"
                          placeholder="Contoh: lantai rusak, dipakai untuk acara">{{ old('notes', $slot->notes) }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Simpan
                </button>
                <a href="{{ route('slots.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
