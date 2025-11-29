<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\User;

class UserRepository {

    // 1. Función datos de usuario
    public function userData()
    {
        $query = new User();
    
        return $query;

    }

    // 2. Crear usuario
    public function userMod($usuario, $correo, $password, $nombres, $apellido_1, $apellido_2, 
    $tipo_documento, $documento, $fecha_nacimiento, $tipo_genero_id, 
    $ciudad_id, $etnia_id, $telefono, $estado_id, $role)
    {
        $data = [

            'correo'=> $correo,
            'password' => $password,
            'codigo_aspirante' => $documento."-".date("Y"),
            'nombres'=> $nombres,
            'apellido_1'=> $apellido_1,
            'apellido_2'=> $apellido_2,
            'tipo_documento'=> $tipo_documento,
            'documento'=> $documento,
            'fecha_nacimiento'=> $fecha_nacimiento,
            'tipo_genero_id'=> $tipo_genero_id,
            'ciudad_id' => $ciudad_id,
            'etnia_id'=> $etnia_id, // Grupo poblacional
            'telefono'=> $telefono,
            'estado_id'=> $estado_id,
            
        ];

        // Registro o actualización del usuario
        if($usuario != null){

            $usuario->update($data);
            $usuario->syncRoles($role);

        }
        else{

            $usuario = User::create($data);
            $usuario->assignRole($role);
            // $usuario->givePermissionTo($role);

        }
       
        return $usuario;

    }

    // 3. Crear perfil y se asocia al usuario registrado
    /*public function aspirante(User $usuario, $nombres, $apellido_1, $apellido_2, 
                                 $tipo_documento, $documento, $fecha_nacimiento, $tipo_genero_id, 
                                 $ciudad_id, $etnia_id, $telefono, $estado_id)
    {
        
        $data = [

            'codigo_aspirante' => $documento."-".date("Y"),
            'nombres'=> $nombres,
            'apellido_1'=> $apellido_1,
            'apellido_2'=> $apellido_2,
            'tipo_documento'=> $tipo_documento,
            'documento'=> $documento,
            'fecha_nacimiento'=> $fecha_nacimiento,
            'tipo_genero_id'=> $tipo_genero_id,
            'ciudad_id' => $ciudad_id,
            'etnia_id'=> $etnia_id, // Grupo poblacional
            'telefono'=> $telefono,
            'estado_id'=> $estado_id,

        ];

        // Perfil de usuario
        $usAsp = $usuario->aspirantes();
        
        $aspirante = $usAsp->updateOrCreate([

            'user_id' => $usuario->id,

        ], $data);

        return $aspirante;

    }*/

}