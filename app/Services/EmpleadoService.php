<?php
namespace App\Services;

use App\Models\Empleado;
use App\Models\Tercero;
use App\Models\TipoContrato;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class EmpleadoService
{
    /**
     * Obtener todos los empleados con su cargo
     */
    public function obtenerEmpleados()
    {
        return Empleado::with('cargo','tipoContrato')->where('company_id', $this->getCompanyId())->orderBy('nombres', 'asc')->get();
    }

    /**
     * Crear un nuevo empleado
     */
    public function crearEmpleado(array $datos)
    {
        try {
            $datos['company_id'] = $this->getCompanyId(); // Asegura que el empleado se asocie a la empresa del usuario autenticado
            $datos['fecha_ingreso'] = $datos['fecha_ingreso'] ?? now()->toDateString(); // Si no se proporciona fecha de ingreso, usar la fecha actual

            $tipoContrato = TipoContrato::where('company_id', $this->getCompanyId())->where('codigo', $datos['tipo_contrato'])->first();
            Tercero::create([
                'company_id' => $datos['company_id'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'tipoidentificacion_id' => $datos['tipo_identificacion_id'],
                'identificacion' => $datos['identificacion'] ?? null,
                'dv' => $datos['dv'] ?? null,
                'ciudad_id' => $datos['ciudad_id'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'celular' => $datos['celular'] ?? null,
                'correo' => $datos['correo'] ?? null,
                'correo_fe'=> $datos['correo'] ?? null,
                'tercerotipo_id' => $tipoContrato?$tipoContrato->tercerotipo_id:2, // 2 = Empleados
                'tipopersona_id' => $datos['tipo_identificacion_id']==1 ? 1 : 2, // 1 = Persona Natural
                'user_id' => auth()->id(),
            ]);
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
        return Empleado::where('id', $id)->where('company_id', $this->getCompanyId())->firstOrFail();
    }

    /**
     * Actualizar un empleado
     */
    public function actualizarEmpleado($id, array $datos)
    {
        try {
            $empleado = Empleado::where('id',$id)->where('company_id', $this->getCompanyId())->firstOrFail();
            $datos['company_id'] = $this->getCompanyId(); // Asegura que el empleado se asocie a la empresa del usuario autenticado
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
            $empleado = Empleado::where('id', $id)->where('company_id', $this->getCompanyId())->firstOrFail();
            $empleado->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception('Error al eliminar el empleado: ' . $e->getMessage());
        }
    }

    private function getCompanyId(): int
    {
        $companyId = session('company_id') ?? auth()->user()?->company_id;

        if (!$companyId) {
            Log::error('Usuario sin company_id', [
                'user_id' => auth()->id(),
                'session' => session()->all()
            ]);
            abort(403, 'No tienes una empresa asignada.');
        }

        return (int) $companyId;
    }
}
