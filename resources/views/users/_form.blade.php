@php
    $edit   = $user->exists;
    $sendiri = $edit && auth()->id() === $user->id;
@endphp

<div class="mb-3">
    <label for="name" class="form-label">Nama Lengkap</label>
    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
           class="form-control @error('name') is-invalid @enderror" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
           class="form-control @error('email') is-invalid @enderror" required>
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">Kata Sandi</label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" {{ $edit ? '' : 'required' }}>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">
            {{ $edit ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : 'Minimal 8 karakter.' }}
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password" class="form-control">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="role" class="form-label">Peran</label>
        @if ($sendiri)
            <input type="hidden" name="role" value="{{ $user->role }}">
            <select id="role" class="form-select" disabled>
                <option>{{ ucfirst($user->role) }}</option>
            </select>
            <div class="form-text">Anda tidak dapat mengubah peran akun sendiri.</div>
        @else
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="petugas" @selected(old('role', $user->role) === 'petugas')>Petugas</option>
                <option value="admin"   @selected(old('role', $user->role) === 'admin')>Admin</option>
            </select>
            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label d-block">Status Akun</label>
        @if ($sendiri)
            <input type="hidden" name="is_active" value="1">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" checked disabled>
                <label class="form-check-label">Aktif</label>
            </div>
            <div class="form-text">Anda tidak dapat menonaktifkan akun sendiri.</div>
        @else
            <input type="hidden" name="is_active" value="0">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                       @checked(old('is_active', $user->is_active))>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>
            <div class="form-text">Akun nonaktif tidak dapat masuk ke sistem.</div>
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>Simpan
    </button>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
