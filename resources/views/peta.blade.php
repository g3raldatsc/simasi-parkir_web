@extends('layouts.public')

@section('title', 'Peta Parkir Interaktif')

@push('styles')
<style>
    /* Styling Peta Grid Interaktif SIMASI-PARKIR */
    .map-wrapper {
        background-color: #f8fafc;
        border: 2px solid #cbd5e1;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
    }

    .parking-grid-container {
        display: grid;
        grid-template-columns: 32px repeat(13, minmax(38px, 48px)) 32px;
        grid-template-rows: repeat(14, minmax(38px, 44px)) minmax(42px, 50px) 28px;
        gap: 4px;
        width: max-content;
        margin: 0 auto;
        user-select: none;
    }

    /* Grid coordinate labels */
    .axis-label {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
    }

    /* Cell Types */
    .grid-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        position: relative;
    }

    /* Road Cell */
    .cell-road {
        background-color: #ffffff;
        border: 1px dashed #cbd5e1;
        color: #94a3b8;
    }

    /* Slot Cell */
    .cell-slot {
        cursor: pointer;
        border: 2px solid transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }
    .cell-slot.slot-available {
        background-color: #10b981;
        color: #ffffff;
        border-color: #059669;
    }
    .cell-slot.slot-available:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 12px rgba(16, 185, 129, 0.35);
        z-index: 10;
    }
    .cell-slot.slot-occupied {
        background-color: #ef4444;
        color: #ffffff;
        border-color: #dc2626;
    }
    .cell-slot.slot-occupied:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 12px rgba(239, 68, 68, 0.35);
        z-index: 10;
    }

    /* Obstacle Cell */
    .cell-obstacle {
        background-color: #334155;
        color: #f8fafc;
        border: 1px solid #1e293b;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Entrance Cell */
    .cell-entrance {
        background-color: #ecfdf5;
        border: 3px solid #10b981;
        color: #047857;
        font-weight: 700;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
        animation: pulse-entrance 2s infinite;
    }
    @keyframes pulse-entrance {
        0%, 100% { box-shadow: 0 0 8px rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 18px rgba(16, 185, 129, 0.8); }
    }

    /* BFS Path animation & highlight */
    .cell-road.path-step {
        background-color: #3b82f6 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.6);
        animation: path-pop 0.3s ease-out;
        z-index: 5;
    }
    .cell-slot.path-target {
        outline: 4px solid #f59e0b !important;
        outline-offset: 2px;
        animation: target-bounce 1s infinite alternate;
        z-index: 15;
    }
    @keyframes target-bounce {
        from { transform: scale(1.05); }
        to { transform: scale(1.15); box-shadow: 0 0 15px rgba(245, 158, 11, 0.8); }
    }
    @keyframes path-pop {
        0% { transform: scale(0.6); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Legend pills */
    .legend-box {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
        margin-right: 6px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header & Summary Cards -->
    <div class="row align-items-center mb-4">
        <div class="col-lg-6">
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-map-fill text-primary me-2"></i>Peta Parkir FASILKOM UNEJ
            </h1>
        </div>
        <div class="col-lg-6 mt-3 mt-lg-0 text-lg-end">
            <button id="btnNearestSlot" class="btn btn-success me-2 shadow-sm">
                <i class="bi bi-compass me-1"></i>Rekomendasi Slot Terdekat
            </button>
            <button id="btnResetPath" class="btn btn-outline-secondary shadow-sm" disabled>
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Rute
            </button>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 me-3">
                        <i class="bi bi-grid-3x3-gap-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold">Total Slot</div>
                        <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success-subtle text-success p-3 rounded-3 me-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold">Slot Tersedia</div>
                        <div class="fs-4 fw-bold text-success">{{ $stats['available'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger-subtle text-danger p-3 rounded-3 me-3">
                        <i class="bi bi-x-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold">Slot Terisi</div>
                        <div class="fs-4 fw-bold text-danger">{{ $stats['occupied'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info-subtle text-info-emphasis p-3 rounded-3 me-3">
                        <i class="bi bi-pie-chart-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-semibold">Tingkat Okupansi</div>
                        <div class="fs-4 fw-bold text-dark">{{ $stats['percentage'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace: Map & Info -->
    <div class="row g-4">
        <div class="col-xl-9">
            <div class="map-wrapper">
                <!-- Legend -->
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center mb-3 p-2 bg-white rounded-3 shadow-sm border">
                    <span class="small fw-semibold text-secondary">Keterangan:</span>
                    <span class="small"><span class="legend-box" style="border: 2px solid #10b981; background: #ecfdf5;"></span>Gerbang (8, 0)</span>
                    <span class="small"><span class="legend-box" style="background: #10b981;"></span>Tersedia</span>
                    <span class="small"><span class="legend-box" style="background: #ef4444;"></span>Terisi</span>
                    <span class="small"><span class="legend-box" style="background: #3b82f6;"></span>Jalur BFS</span>
                    <span class="small"><span class="legend-box" style="background: #ffffff; border: 1px dashed #cbd5e1;"></span>Jalur Lewat</span>
                    <span class="small"><span class="legend-box" style="background: #334155;"></span>Gedung / Rintangan</span>
                </div>

                <!-- Grid Canvas -->
                <div class="parking-grid-container" id="parkingGrid">
                    {{-- Row 14 down to 1 --}}
                    @for ($y = 14; $y >= 1; $y--)
                        {{-- Left Axis Label (Block letter / Row number) --}}
                        <div class="axis-label" style="grid-column: 1; grid-row: {{ 15 - $y }};">
                            @php
                                $leftLabel = match($y) {
                                    14 => 'K',
                                    12 => 'I',
                                    10 => 'G',
                                    8 => 'E',
                                    6 => 'C',
                                    4 => 'B',
                                    2 => 'A',
                                    default => ''
                                };
                            @endphp
                            <span class="fw-bold text-dark me-1">{{ $leftLabel }}</span>
                            <span class="text-muted small">{{ $y }}</span>
                        </div>

                        {{-- Right Axis Label --}}
                        <div class="axis-label" style="grid-column: 15; grid-row: {{ 15 - $y }};">
                            @php
                                $rightLabel = match($y) {
                                    12 => 'J',
                                    10 => 'H',
                                    8 => 'F',
                                    6 => 'D',
                                    default => ''
                                };
                            @endphp
                            <span class="fw-bold text-dark me-1">{{ $rightLabel }}</span>
                            <span class="text-muted small">{{ $y }}</span>
                        </div>
                    @endfor

                    {{-- Bottom Column Axis (X: 1 to 13) --}}
                    @for ($x = 1; $x <= 13; $x++)
                        <div class="axis-label" style="grid-column: {{ $x + 1 }}; grid-row: 16;">
                            <span class="fw-bold {{ $x === 8 ? 'text-success fs-6' : 'text-secondary' }}">{{ $x }}</span>
                        </div>
                    @endfor

                    {{-- Obstacles (Merged Visual Blocks) --}}
                    {{-- Gedung: X=9..13, Y=14 (CSS row: 1) --}}
                    <div class="grid-cell cell-obstacle"
                         style="grid-column: 10 / span 5; grid-row: 1;"
                         title="Gedung">
                        <i class="bi bi-building fs-5 mb-1"></i>
                        <span>Gedung</span>
                    </div>

                    {{-- Gudang: X=9..13, Y=3..4 (CSS row: 11 / span 2) --}}
                    <div class="grid-cell cell-obstacle"
                         style="grid-column: 10 / span 5; grid-row: 11 / span 2;"
                         title="Gudang">
                        <i class="bi bi-box-seam fs-5 mb-1"></i>
                        <span>Gudang</span>
                    </div>

                    {{-- Pos: X=9..13, Y=1..2 (CSS row: 13 / span 2) --}}
                    <div class="grid-cell cell-obstacle"
                         style="grid-column: 10 / span 5; grid-row: 13 / span 2;"
                         title="Pos Jaga">
                        <i class="bi bi-shield-check fs-5 mb-1"></i>
                        <span>Pos</span>
                    </div>

                    {{-- Cells: Slots and Roads --}}
                    @foreach ($cells as $cell)
                        @php
                            $cssCol = $cell['x'] + 1; // offset by 1 for left axis
                            $cssRow = 15 - $cell['y']; // Cartesian to CSS Grid
                        @endphp

                        @if ($cell['type'] === 'entrance')
                            {{-- Gerbang Entrance at (8, 0) -> CSS row 15, column 9 --}}
                            <div class="grid-cell cell-entrance"
                                 id="cell-{{ $cell['x'] }}-{{ $cell['y'] }}"
                                 data-x="{{ $cell['x'] }}"
                                 data-y="{{ $cell['y'] }}"
                                 style="grid-column: {{ $cssCol }}; grid-row: 15;"
                                 title="Gerbang Masuk (8, 0)">
                                <i class="bi bi-door-open-fill fs-6"></i>
                                <span style="font-size: 0.65rem;">Gerbang Parkir</span>
                            </div>
                        @elseif ($cell['type'] === 'slot')
                            @php
                                $slot = $cell['slot'];
                                $isOccupied = $slot && $slot['is_occupied'];
                            @endphp
                            <div class="grid-cell cell-slot {{ $isOccupied ? 'slot-occupied' : 'slot-available' }}"
                                 id="cell-{{ $cell['x'] }}-{{ $cell['y'] }}"
                                 data-x="{{ $cell['x'] }}"
                                 data-y="{{ $cell['y'] }}"
                                 data-slot-id="{{ $slot['id'] ?? '' }}"
                                 data-label="{{ $cell['label'] }}"
                                 data-block="{{ $slot['block_code'] ?? '' }}"
                                 data-block-name="{{ $slot['block_name'] ?? '' }}"
                                 data-number="{{ $slot['number'] ?? '' }}"
                                 data-occupied="{{ $isOccupied ? '1' : '0' }}"
                                 data-plate="{{ $slot['vehicle_plate'] ?? '' }}"
                                 style="grid-column: {{ $cssCol }}; grid-row: {{ $cssRow }};"
                                 title="Slot {{ $cell['label'] }} ({{ $isOccupied ? 'Terisi: ' . ($slot['vehicle_plate'] ?? '') : 'Tersedia' }})">
                                <span style="font-size: 0.7rem;">{{ $cell['label'] }}</span>
                                @if ($isOccupied)
                                    <i class="bi bi-motorcycle" style="font-size: 0.65rem;"></i>
                                @endif
                            </div>
                        @elseif ($cell['type'] === 'road')
                            <div class="grid-cell cell-road"
                                 id="cell-{{ $cell['x'] }}-{{ $cell['y'] }}"
                                 data-x="{{ $cell['x'] }}"
                                 data-y="{{ $cell['y'] }}"
                                 style="grid-column: {{ $cssCol }}; grid-row: {{ $cssRow }};">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Panel: Detail & Navigasi -->
        <div class="col-xl-3">
            <!-- Selected Slot Information Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-dark mb-0 fs-6">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Slot
                    </h5>
                </div>
                <div class="card-body" id="slotDetailPanel">
                    <div class="text-center text-muted py-4" id="emptySlotPlaceholder">
                        <i class="bi bi-cursor text-secondary display-6 d-block mb-2"></i>
                        <p class="small mb-0">Klik salah satu slot parkir pada peta untuk melihat informasi & rute navigasi.</p>
                    </div>

                    <div id="slotDetailContent" class="d-none">
                        <div class="text-center mb-3">
                            <span class="badge fs-5 px-3 py-2 rounded-pill" id="detailStatusBadge"></span>
                            <h3 class="fw-bold text-dark mt-2 mb-0" id="detailSlotLabel"></h3>
                            <div class="text-muted small" id="detailBlockName"></div>
                        </div>

                        <ul class="list-group list-group-flush mb-3 small">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Koordinat Grid:</span>
                                <span class="fw-semibold text-dark" id="detailCoords"></span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between" id="detailPlateRow">
                                <span class="text-muted">Plat Kendaraan:</span>
                                <span class="fw-semibold text-danger" id="detailPlate"></span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">Jarak dari Gerbang:</span>
                                <span class="fw-semibold text-primary" id="detailDistance">-</span>
                            </li>
                        </ul>

                        <button id="btnShowRouteToSlot" class="btn btn-primary w-100 shadow-sm">
                            <i class="bi bi-signpost-split me-1"></i>Tampilkan Rute BFS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Block Occupancy Overview Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-dark mb-0 fs-6">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Ketersediaan per Blok
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 380px; overflow-y: auto;">
                        @foreach ($stats['blocks'] as $code => $blockStat)
                            <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-dark">{{ $blockStat['name'] }}</span>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        {{ $blockStat['available'] }} kosong dari {{ $blockStat['total'] }} slot
                                    </div>
                                </div>
                                <span class="badge {{ $blockStat['available'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                    {{ $blockStat['available'] }}/{{ $blockStat['total'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentPath = [];
    let selectedSlot = null;

    // BFS Pathfinding Algorithm in JavaScript (instant client-side, with API fallback)
    function clearPath() {
        $('.cell-road').removeClass('path-step').text('');
        $('.cell-slot').removeClass('path-target');
        currentPath = [];
        $('#btnResetPath').prop('disabled', true);
    }

    function renderPath(path, targetSlotId) {
        clearPath();
        currentPath = path;

        path.forEach((step, index) => {
            const cellId = `#cell-${step.x}-${step.y}`;
            const $cell = $(cellId);

            if ($cell.hasClass('cell-road')) {
                $cell.addClass('path-step');
                $cell.text(index); // show step counter
            } else if ($cell.hasClass('cell-slot')) {
                $cell.addClass('path-target');
            }
        });

        $('#btnResetPath').prop('disabled', false);
    }

    // Click on Slot cell
    $('.cell-slot').on('click', function() {
        const $this = $(this);
        const x = parseInt($this.data('x'));
        const y = parseInt($this.data('y'));
        const slotId = $this.data('slot-id');
        const label = $this.data('label');
        const blockName = $this.data('block-name');
        const isOccupied = $this.data('occupied') == '1';
        const plate = $this.data('plate');

        selectedSlot = { x, y, slotId, label, blockName, isOccupied, plate };

        // Update detail panel
        $('#emptySlotPlaceholder').addClass('d-none');
        $('#slotDetailContent').removeClass('d-none');
        $('#detailSlotLabel').text('Slot ' + label);
        $('#detailBlockName').text(blockName);
        $('#detailCoords').text(`(${x}, ${y})`);

        if (isOccupied) {
            $('#detailStatusBadge').removeClass('bg-success').addClass('bg-danger').text('Terisi');
            $('#detailPlateRow').removeClass('d-none');
            $('#detailPlate').text(plate || 'Tidak diketahui');
        } else {
            $('#detailStatusBadge').removeClass('bg-danger').addClass('bg-success').text('Tersedia');
            $('#detailPlateRow').addClass('d-none');
        }

        // Fetch route via API
        fetchRoute(x, y, slotId);
    });

    function fetchRoute(x, y, slotId) {
        $('#detailDistance').html('<span class="spinner-border spinner-border-sm" role="status"></span> Menghitung...');

        $.getJSON('{{ route("peta.path") }}', { x: x, y: y, slot_id: slotId })
            .done(function(response) {
                if (response.success) {
                    $('#detailDistance').text(`${response.distance} langkah (${response.distance * 2} meter est.)`);
                    renderPath(response.path, slotId);
                }
            })
            .fail(function(xhr) {
                $('#detailDistance').text('Rute tidak terjangkau');
                clearPath();
            });
    }

    // Button: Show Route
    $('#btnShowRouteToSlot').on('click', function() {
        if (selectedSlot) {
            fetchRoute(selectedSlot.x, selectedSlot.y, selectedSlot.slotId);
        }
    });

    // Button: Reset Route
    $('#btnResetPath').on('click', function() {
        clearPath();
    });

    // Button: Nearest Available Slot
    $('#btnNearestSlot').on('click', function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Mencari...').prop('disabled', true);

        $.getJSON('{{ route("peta.nearest") }}')
            .done(function(response) {
                $btn.html(originalHtml).prop('disabled', false);
                if (response.success && response.data) {
                    const nearest = response.data;
                    const cellId = `#cell-${nearest.x}-${nearest.y}`;
                    const $cell = $(cellId);

                    if ($cell.length) {
                        $cell.trigger('click');
                        $('html, body').animate({
                            scrollTop: $cell.offset().top - 150
                        }, 500);
                    }
                }
            })
            .fail(function(xhr) {
                $btn.html(originalHtml).prop('disabled', false);
                alert(xhr.responseJSON?.message || 'Tidak ada slot kosong yang tersedia.');
            });
    });
});
</script>
@endpush
