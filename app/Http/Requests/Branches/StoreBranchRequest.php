<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => 'nullable|string|max:30',
            'name' => 'required|string|max:120',
            'address' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ];
    }
}
