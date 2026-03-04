<?php

namespace App\Http\Requests\CostCenters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCostCenterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:cost_centers,id',
            'is_active' => 'required|boolean',
        ];
    }
}
