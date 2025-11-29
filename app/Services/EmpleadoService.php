<?php
namespace App\Services;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Exception;

class EmpleadoService
{
    /**
     * Obtener todos los empleados con su cargo
     */
    public function obtenerEmpleados()
    {
        return Empleado::with('cargo','tipoContrato')->orderBy('nombres', 'asc')->get();
    }

    /**
     * Crear un nuevo empleado
     */
    public function crearEmpleado(array $datos)
    {
        try {
            return Empleado::create($datos);
        } catch (Exception $e) {
            throw new Exception('Error al crear el empleado: ' . $e->getMessage());
        }
    }

    /**
     * Obtener un empleado por ID
     */
    public function obtenerEmpleadoPorId($id)
    {
        return Empleado::findOrFail($id);
    }

    /**
     * Actualizar un empleado
     */
    public function actualizarEmpleado($id, array $datos)
    {
        try {
            $empleado = Empleado::where('id',$id)->first();
            $empleado->update($datos);
            return $empleado;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar el empleado: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un empleado
     */
    public function eliminarEmpleado($id)
    {
        try {
            $empleado = Empleado::findOrFail($id);
            $empleado->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception('Error al eliminar el empleado: ' . $e->getMessage());
        }
    }
}
