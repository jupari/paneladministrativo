<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WorkshopController extends Controller
{
    private function resolveCompanyId(): int
    {
        return (int) (session('company_id') ?? auth()->user()?->company_id ?? 0);
    }

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.workshops.index');
        }

        $companyId = $this->resolveCompanyId();
        // return response()->json($companyId);
        $query = Workshop::query()
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->select([
                'workshops.id',
                'workshops.name',
                'workshops.code',
                'workshops.address',
                'workshops.coordinator_name',
                'workshops.coordinator_phone',
                'workshops.status',
                'workshops.last_sync_at',
                'workshops.created_at',
            ]);

        // return response()->json($query->toSql()); // Para debug: mostrar la consulta SQL generada
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('devices_count', fn ($r) => (int) $r->devices()->count())
            ->editColumn('status', function ($r) {
                $color = match ($r->status) {
                    'active' => 'success',
                    'suspended' => 'warning',
                    default => 'secondary',
                };

                return "<span class='badge badge-{$color}'>" . strtoupper($r->status) . '</span>';
            })
            ->addColumn('acciones', function ($r) {
                $showUrl = route('admin.produccion.workshops.show', $r->id);

                return '
                    <a href="' . $showUrl . '" class="btn btn-sm btn-info" title="Ver detalle"><i class="fas fa-eye"></i></a>
                    <button class="btn btn-sm btn-primary" onclick="editWorkshop(' . $r->id . ')" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-warning" onclick="toggleWorkshopStatus(' . $r->id . ')" title="Cambiar estado"><i class="fas fa-power-off"></i></button>
                ';
            })
            ->rawColumns(['status', 'acciones'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:20|unique:workshops,code',
            'address' => 'nullable|string|max:255',
            'coordinator_name' => 'nullable|string|max:120',
            'coordinator_phone' => 'nullable|string|max:30',
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        $companyId = $this->resolveCompanyId();

        $workshop = Workshop::query()->create($data);
        $workshop->companies()->syncWithoutDetaching([$companyId]);

        return response()->json([
            'message' => 'Taller creado correctamente.',
            'id' => $workshop->id,
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $companyId = $this->resolveCompanyId();

        $workshop = Workshop::query()
            ->where('id', $id)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->firstOrFail();

        return response()->json(['data' => $workshop]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $companyId = $this->resolveCompanyId();

        $workshop = Workshop::query()
            ->where('id', $id)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:20|unique:workshops,code,' . $workshop->id,
            'address' => 'nullable|string|max:255',
            'coordinator_name' => 'nullable|string|max:120',
            'coordinator_phone' => 'nullable|string|max:30',
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        $workshop->update($data);

        return response()->json(['message' => 'Taller actualizado correctamente.']);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $companyId = $this->resolveCompanyId();

        $workshop = Workshop::query()
            ->where('id', $id)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->firstOrFail();

        $nextStatus = match ($workshop->status) {
            'active' => 'inactive',
            'inactive' => 'active',
            'suspended' => 'active',
            default => 'inactive',
        };

        $workshop->update(['status' => $nextStatus]);

        return response()->json([
            'message' => 'Estado actualizado a ' . strtoupper($nextStatus) . '.',
            'status' => $nextStatus,
        ]);
    }

    public function show(int $id)
    {
        $companyId = $this->resolveCompanyId();

        $workshop = Workshop::query()
            ->where('id', $id)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->firstOrFail();

        $devicesCount = $workshop->devices()->where('company_id', $companyId)->count();
        $activeDevicesCount = $workshop->devices()->where('company_id', $companyId)->where('status', 'active')->count();

        return view('produccion.workshops.show', compact('workshop', 'devicesCount', 'activeDevicesCount'));
    }
}
