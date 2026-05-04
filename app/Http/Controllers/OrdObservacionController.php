<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrdObservacionRequest;
use App\Services\OrdObservacionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrdObservacionController extends Controller
{
    protected $observacionService;

    public function __construct(OrdObservacionService $observacionService)
    {
        $this->observacionService = $observacionService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $observaciones = $this->observacionService->getAll();
            return DataTables::of($observaciones)
                ->addColumn('estado', function($obs) {
                    $badgeClass = $obs->active ? 'success' : 'danger';
                    $text = $obs->active ? 'Activo' : 'Inactivo';
                    return '<span class="badge badge-' . $badgeClass . '">' . $text . '</span>';
                })
                ->addColumn('acciones', function($obs) {
                    $btnClass = $obs->active ? 'danger' : 'success';
                    $iconClass = $obs->active ? 'ban' : 'check';
                    $title = $obs->active ? 'Desactivar' : 'Activar';
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $route = route('admin.observaciones.destroy', $obs->id);
                    $obsJson = htmlspecialchars(json_encode($obs), ENT_QUOTES, 'UTF-8');

                    $html = '<div class="d-flex justify-content-center">';

                    if (auth()->user()->hasRole(['Administrator']) || auth()->user()->can('cotizaciones.observaciones.edit')) {
                        $html .= '
                            <button class="btn btn-sm btn-info mr-1" onclick="editObservacion(' . $obsJson . ')" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>';
                    }

                    if (auth()->user()->hasRole(['Administrator']) || auth()->user()->can('cotizaciones.observaciones.destroy')) {
                        $html .= '
                            <form action="' . $route . '" method="POST" style="display:inline;">
                                ' . $csrf . '
                                ' . $method . '
                                <button type="submit" class="btn btn-sm btn-' . $btnClass . '" title="' . $title . '">
                                    <i class="fas fa-' . $iconClass . '"></i>
                                </button>
                            </form>';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['estado', 'acciones'])
                ->make(true);
        }

        return view('cotizar.observaciones.index');
    }

    public function store(OrdObservacionRequest $request)
    {
        $this->observacionService->create($request->validated());
        return redirect()->route('admin.observaciones.index')->with('success', 'Observación creada exitosamente.');
    }

    public function update(OrdObservacionRequest $request, $id)
    {
        $this->observacionService->update($id, $request->validated());
        return redirect()->route('admin.observaciones.index')->with('success', 'Observación actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $this->observacionService->toggleActive($id);
        return redirect()->route('admin.observaciones.index')->with('success', 'Estado modificado exitosamente.');
    }
}
