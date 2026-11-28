<div class="mb-3">
    <label for="plate_number" class="form-label">Nomor Plat</label>
    <input type="text" id="plate_number" name="plate_number"
           value="{{ old('plate_number', $vehicle->plate_number) }}"
           class="form-control font-monospace text-uppercase @error('plate_number') is-invalid @enderror"
           maxlength="15" placeholder="Contoh: P1234XL" required>
    @error('plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Spasi dan tanda hubung dihapus otomatis. "P 1234 XL" disimpan sebagai P1234XL.</div>
</div>

<div class="mb-3">
    <label for="owner_name" class="form-label">Nama Pemilik <span class="text-body-secondary">(opsional)</span></label>
    <input type="text" id="owner_name" name="owner_name"
           value="{{ old('owner_name', $vehicle->owner_name) }}"
           class="form-control @error('owner_name') is-invalid @enderror" maxlength="100">
    @error('owner_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>Simpan
    </button>
    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>

@push('scripts')
<script>
    // Rapikan plat saat kolom ditinggalkan. Server tetap menormalisasi ulang.
    $('#plate_number').on('blur', function () {
        $(this).val($(this).val().replace(/[^A-Za-z0-9]/g, '').toUpperCase());
    });
</script>
@endpush
