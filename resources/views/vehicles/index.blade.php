@extends('layouts.panel')

@section('title', 'Data Kendaraan')
@section('heading', 'Data Kendaraan')

@section('actions')
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kendaraan
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('vehicles.index') }}" class="row g-2 mb-3">
            <div class="col-sm-8 col-md-6 col-lg-4">
                <input type="search" name="q" value="{{ $q }}" class="form-control"
                       placeholder="Cari nomor plat atau nama pemilik">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                @if ($q !== '')
                    <a href="{{ route('vehicles.index') }}" class="btn btn-link">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Nomor Plat</th>
                        <th>Pemilik</th>
                        <th>Status Parkir</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($vehicles as $v)
                    <tr>
                        <td>{{ $vehicles->firstItem() + $loop->index }}</td>
                        <td class="font-monospace fw-semibold">{{ $v->plate_number }}</td>
                        <td>{{ $v->owner_name ?? '-' }}</td>
                        <td>
                            @if ($v->relationLoaded('activeTransaction'))
                                @if ($v->activeTransaction)
                                    <span class="badge text-bg-danger">
                                        Terparkir di {{ $v->activeTransaction->slot->label }}
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">Tidak parkir</span>
                                @endif
                            @else
                                <span class="text-body-secondary">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('vehicles.edit', $v) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Ubah
                            </a>
                            <form method="POST" action="{{ route('vehicles.destroy', $v) }}" class="d-inline"
                                  data-confirm="Hapus kendaraan {{ $v->plate_number }}? Tindakan ini tidak dapat dibatalkan.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-4">
                            {{ $q !== '' ? 'Tidak ada kendaraan yang cocok.' : 'Belum ada data kendaraan.' }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $vehicles->links() }}
        </div>
    </div>
</div>
@endsection
