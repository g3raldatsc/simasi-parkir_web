@extends('layouts.panel')

@section('title', 'Slot')
@section('heading', 'Slot Parkir')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('slots.index') }}" class="row g-2 mb-3">
            <div class="col-sm-4 col-md-3">
                <select name="blok" class="form-select">
                    <option value="">Semua blok</option>
                    @foreach ($blocks as $b)
                        <option value="{{ $b->id }}" @selected($blokId === $b->id)>
                            {{ $b->code }} - {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="aktif"    @selected($status === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-funnel me-1"></i>Terapkan
                </button>
                @if ($blokId || $status)
                    <a href="{{ route('slots.index') }}" class="btn btn-link">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Slot</th>
                        <th>Koordinat</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($slots as $slot)
                    @php
                        $dimuat = $slot->relationLoaded('activeTransaction');
                        $terisi = $dimuat && $slot->activeTransaction;
                    @endphp
                    <tr>
                        <td>{{ $slots->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $slot->label }}</td>
                        <td>({{ $slot->mapCell->x + 1 }}, {{ $slot->mapCell->y + 1 }})</td>
                        <td>
                            @if (! $slot->is_active)
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            @elseif ($terisi)
                                <span class="badge text-bg-danger">Terisi</span>
                            @elseif ($dimuat)
                                <span class="badge text-bg-success">Kosong</span>
                            @else
                                <span class="badge text-bg-success">Aktif</span>
                            @endif
                        </td>
                        <td class="text-body-secondary small">{{ $slot->notes ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('slots.edit', $slot) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Ubah
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-body-secondary py-4">
                            Tidak ada slot yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $slots->links() }}
        </div>
    </div>
</div>
@endsection
