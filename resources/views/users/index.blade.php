@extends('layouts.panel')

@section('title', 'Pengguna')
@section('heading', 'Pengguna')

@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Pengguna
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 mb-3">
            <div class="col-sm-8 col-md-6 col-lg-4">
                <input type="search" name="q" value="{{ $q }}" class="form-control"
                       placeholder="Cari nama atau email">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                @if ($q !== '')
                    <a href="{{ route('users.index') }}" class="btn btn-link">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>
                            {{ $u->name }}
                            @if ($u->id === auth()->id())
                                <span class="badge text-bg-info">Anda</span>
                            @endif
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <span class="badge {{ $u->isAdmin() ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                {{ ucfirst($u->role) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $u->is_active ? 'text-bg-success' : 'text-bg-danger' }}">
                                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Ubah
                            </a>
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline"
                                      data-confirm="Hapus pengguna {{ $u->name }}? Tindakan ini tidak dapat dibatalkan.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-body-secondary py-4">
                            Tidak ada pengguna yang cocok.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
