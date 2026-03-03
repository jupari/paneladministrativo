<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdWorkerLogRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_operation_id' => ['required','integer'],
            'employee_ids' => ['required','array','min:1'],
            'employee_ids.*' => ['integer'],
            'qty' => ['required','numeric','gt:0'],
            'worked_at' => ['required','date'],
            'notes' => ['nullable','string','max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Debes seleccionar al menos un empleado.',
            'qty.gt' => 'La cantidad debe ser mayor a 0.',
        ];
    }
}
