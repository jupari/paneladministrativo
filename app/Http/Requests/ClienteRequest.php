<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permitir acceso
    }

    public function rules()
    {
        return [
            'tercerotipo_id' => 'required|exists:terceros_tipos,id',
            'tipoidentificacion_id' => 'required|exists:tipo_identificacion,id',
            'identificacion' => 'required|string|max:20|unique:terceros,identificacion,' . $this->route('id'),
            'dv' => 'nullable|string|max:1',
            'tipopersona_id' => 'required|exists:tipo_persona,id',
            'nombres' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->input('tipopersona_id') == 'natural' && empty($value)) {
                        $fail('El campo nombres es obligatorio para personas naturales.');
                    }
                },
            ],
            'apellidos' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->input('tipopersona_id') == 'natural' && empty($value)) {
                        $fail('El campo apellidos es obligatorio para personas naturales.');
                    }
                },
            ],
            'nombre_establecimiento' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->input('tipopersona_id') == 'juridico' && empty($value)) {
                        $fail('El campo nombre del establecimiento es obligatorio para personas jurídicas.');
                    }
                },
            ],
            'celular' => 'nullable|regex:/^[0-9]{10}$/',
            'telefono' => 'nullable|regex:/^[0-9]{10}$/',
            'correo' => 'required|email|max:255|unique:terceros,correo,' . $this->route('id'),
            'correo_fe' => 'nullable|email|max:255',
            'ciudad_id' => 'required|exists:ciudades,id',
            'direccion' => 'nullable|string|max:255',
            'vendedor_id' => 'nullable|exists:vendedores,id',
            'user_id' => 'required|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'celular.regex' => 'El número de celular debe tener 10 dígitos numéricos.',
            'telefono.regex' => 'El número de teléfono debe tener 10 dígitos numéricos.',
        ];
    }
}
