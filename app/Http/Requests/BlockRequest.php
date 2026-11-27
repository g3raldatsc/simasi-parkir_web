<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // pembatasan akses ditangani middleware role:admin
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('blocks', 'name')->ignore($this->route('block')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama blok wajib diisi.',
            'name.max'      => 'Nama blok maksimal 100 karakter.',
            'name.unique'   => 'Nama blok sudah digunakan oleh blok lain.',
        ];
    }
}
