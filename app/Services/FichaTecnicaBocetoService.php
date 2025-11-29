<?php

namespace App\Services;

use App\Models\FichaTecnica;
use App\Models\FichaTecnicaBoceto;
use DateTime;
use Exception;
use Yajra\Datatables\Datatables;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;
use Illuminate\Support\Facades\Storage;

class FichaTecnicaBocetoService
{
    public function getAll()
    {
        return FichaTecnicaBoceto::getAll();
    }

    public function getById($id)
    {
        return FichaTecnicaBoceto::where('fichatecnica_id', $id)->get();
    }

    public function store(array $data)
    {
        try {
            $fichatecnicaId = $data['fichatecnica_id'];
            // $bocetosExistentes = FichaTecnicaBoceto::where('fichatecnica_id', $fichatecnicaId)->get();

            // foreach ($bocetosExistentes as $boceto) {
            //     // Eliminar archivo físico si existe
            //     if ($boceto->archivo && Storage::disk('public')->exists($boceto->archivo)) {
            //         Storage::disk('public')->delete($boceto->archivo);
            //     }
            //     // Eliminar registro en BD
            //     $boceto->delete();
            // }
            // Recorremos los arrays nombre[] y archivo[]
            if (isset($data['archivo']) && is_array($data['archivo'])) {
                foreach ($data['archivo'] as $index => $file) {
                    $bocetoid = $data['fichatecnicaboceto_id'][$index]??0;
                    $nombre = $data['nombre'][$index]??'';
                    $observacion = $data['observacion'][$index]??'';
                    $codigo=$data['codigo'][$index]??'';
                    $fichatecnicaIdfile = $data['fichatecnica_id'][$index]??0;
                    // Guardar archivo en storage/app/public/bocetos
                    $path = $file->store('bocetos', 'public');

                     // 1. Eliminar todos los bocetos actuales de la ficha
                    $bocetos = FichaTecnicaBoceto::where('fichatecnica_id', $fichatecnicaId)->get();
                    foreach ($bocetos as $boceto) {
                        if($nombre==$boceto->nombre){
                            // if ($boceto->archivo && Storage::disk('public')->exists($boceto->archivo)) {
                                Storage::disk('public')->delete($boceto->archivo);
                                $boceto->delete();
                            // }
                        }
                    }

                    // Insertar registro en BD
                    FichaTecnicaBoceto::create([
                        'fichatecnica_id' => $fichatecnicaIdfile,
                        'nombre' => $nombre,
                        'archivo' => $path,
                        'codigo' => $codigo, //uniqid('BOC-'), // puedes generar un código único
                        'observacion'=>$observacion
                    ]);
                }
                return true;
            }else {
                // throw new Exception('No se han proporcionado archivos para subir.',500);
            }
        } catch (Exception $e) {
            throw new Exception('Error al crear el boceto de ficha técnica: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $boceto = FichaTecnicaBoceto::findOrFail($id);
            // Eliminar archivo del storage
            if ($boceto->archivo && Storage::disk('public')->exists($boceto->archivo)) {
                Storage::disk('public')->delete($boceto->archivo);
            }
            $boceto->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
