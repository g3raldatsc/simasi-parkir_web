<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // pembatasan akses ditangani middleware auth
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi dulu, baru divalidasi: "p 1234 xl" menjadi "P1234XL"
        if (is_string($this->plate_number)) {
            $this->merge(['plate_number' => Vehicle::normalize($this->plate_number)]);
        }
    }

    public function rules(): array
    {
        return [
            'plate_number' => [
                'required',
                'max:12',
                'regex:' . Vehicle::PLATE_PATTERN,
                Rule::unique('vehicles', 'plate_number')->ignore($this->route('vehicle')),
            ],
            'owner_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate_number.required' => 'Nomor plat wajib diisi.',
            'plate_number.max'      => 'Nomor plat maksimal 12 karakter.',
            'plate_number.regex'    => 'Format nomor plat tidak valid. Contoh yang benar: P1234XL.',
            'plate_number.unique'   => 'Nomor plat sudah terdaftar.',
            'owner_name.max'        => 'Nama pemilik maksimal 100 karakter.',
        ];
    }
}
