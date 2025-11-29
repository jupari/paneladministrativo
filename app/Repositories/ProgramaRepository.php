<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Programa;


class ProgramaRepository {

    // 1. Función datos de usuario
    public function programData()
    {
        $query = new Programa();
    
        return $query;

    }

    // 2. Crear usuario
    public function prograMod($programa, $codigo_programa, $nombre, $descripcion, $director_programa,
                              $contacto, $duracion, $titulo, $enfoque_estudio, $creditos, $estado_id, $modadlidad_id,
                              $jornada_id, $facultad_id, $nivel, $sector_id, $salario_url, $documentos, $postulacion, $brecha_id, $valor_semestre , $beca)
    {

        $data = [

            'codigo_programa' => $codigo_programa,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'director_programa' => $director_programa,
            'contacto' => $contacto,
            'duracion' => $duracion,
            'titulo'   => $titulo, 
            'enfoque_estudio' => $enfoque_estudio,
            'creditos' => $creditos,
            'estado_id' => $estado_id,
            'modadlidad_id' => $modadlidad_id,
            'jornada_id' => $jornada_id,
            'facultad_id' => $facultad_id,
            'nivel_estudios_id' => $nivel,
            'sector_id' => $sector_id,
            'salario_url' => $salario_url,
            'documentos' => $documentos,
            'postulacion' => $postulacion,
            'brecha_id' => $brecha_id,
            'valor_semestre' => $valor_semestre,
            'contiene_beca' => $beca
            
        ];

        // Registro o actualización del usuario
        if($programa != null){

            $programa = $programa->update($data);

        }
        else{

            $programa = Programa::create($data);

        }
       
        return $programa;

    }

}