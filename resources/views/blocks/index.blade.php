@extends('layouts.panel')

@section('title', 'Blok')
@section('heading', 'Blok Parkir')

@section('content')
<div class="card">
    <div class="card-body">
        <p class="text-body-secondary small">
            Bentuk denah dan posisi slot ditetapkan melalui seeder. Pada halaman ini admin hanya
            dapat mengubah nama blok.
        </p>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:80px">Kode</th>
                        <th>Nama Blok</th>
                        <th class="text-center">Jumlah Slot</th>
                        <th class="text-center">Slot Aktif</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($blocks as $block)
                    <tr>
                        <td><span class="badge text-bg-primary fs-6">{{ $block->code }}</span></td>
                        <td>{{ $block->name }}</td>
                        <td class="text-center">{{ $block->slots_count }}</td>
                        <td class="text-center">
                            <span class="badge {{ $block->active_slots_count === $block->slots_count ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $block->active_slots_count }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('slots.index', ['blok' => $block->id]) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-p-square"></i> Lihat Slot
                            </a>
                            <a href="{{ route('blocks.edit', $block) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Ubah
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-4">Belum ada blok.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
