<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:60',
            'product_id' => 'required|integer',
            'objective_qty' => 'required|numeric|min:0.01',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:DRAFT,IN_PROGRESS,CLOSED,CANCELLED',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
