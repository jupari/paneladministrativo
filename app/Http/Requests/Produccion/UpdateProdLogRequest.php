<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProdLogRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer',
            'order_operation_id' => 'required|integer',
            'employee_id' => 'required|integer',
            'work_date' => 'required|date',
            'shift' => 'nullable|in:AM,PM,NIGHT',
            'qty' => 'required|numeric|min:0',
            'rejected_qty' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
