<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'q'));
    }

    public function create(): View
    {
        $user = new User(['role' => User::ROLE_PETUGAS, 'is_active' => true]);

        return view('users.create', compact('user'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Kata sandi kosong = tidak diubah
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        // Admin tidak boleh mengubah peran atau status akunnya sendiri
        if ($user->is($request->user())) {
            unset($data['role'], $data['is_active']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang dipakai.');
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            // SQLSTATE 23000 = pelanggaran foreign key (pengguna sudah punya transaksi)
            if ((string) $e->getCode() === '23000') {
                return back()->with('error',
                    'Pengguna tidak dapat dihapus karena sudah memiliki riwayat transaksi. Nonaktifkan akunnya saja.');
            }
            throw $e;
        }

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
