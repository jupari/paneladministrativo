<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;

class StoreNominaPayRunRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'run_type' => ['required','in:NOMINA,CONTRATISTAS,MIXTO'],
            'period_start' => ['required','date'],
            'period_end' => ['required','date','after_or_equal:period_start'],
            'pay_date' => ['required','date'],
            'notes' => ['nullable','string','max:5000'],

            // selección (por defecto: ambos)
            'include_laboral' => ['sometimes','boolean'],
            'include_contratistas' => ['sometimes','boolean'],
        ];
    }
}
