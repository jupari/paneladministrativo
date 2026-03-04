<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;

class StoreNominaNoveltyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'participant_type' => ['required','in:App\\Models\\Empleado,App\\Models\\Tercero'],
            'participant_id' => ['required','integer'],
            'link_type' => ['required','in:LABORAL,CONTRATISTA'],
            'nomina_concept_id' => ['required','exists:nomina_concepts,id'],
            'period_start' => ['required','date'],
            'period_end' => ['required','date','after_or_equal:period_start'],
            'quantity' => ['nullable','numeric'],
            'amount' => ['nullable','numeric'],
            'description' => ['nullable','string','max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (($this->quantity === null || $this->quantity === '') && ($this->amount === null || $this->amount === '')) {
                $v->errors()->add('amount', 'Debe enviar amount o quantity.');
            }
        });
    }
}
