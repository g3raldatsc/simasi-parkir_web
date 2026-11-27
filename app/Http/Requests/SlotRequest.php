<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Checkbox yang tidak dicentang dibaca sebagai false
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'is_active' => ['boolean'],
            'notes'     => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.boolean' => 'Status slot tidak valid.',
            'notes.max'         => 'Catatan maksimal 255 karakter.',
        ];
    }
}
