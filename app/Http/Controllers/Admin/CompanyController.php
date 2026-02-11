<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function __construct()
    {
        // Solo usuarios con rol sysadmin pueden gestionar empresas
        $this->middleware('sysadmin');
        
        $this->middleware('can:companies.index')->only(['index']);
        $this->middleware('can:companies.create')->only(['create', 'store']);
        $this->middleware('can:companies.edit')->only(['edit', 'update']);
        $this->middleware('can:companies.destroy')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                // Verificar si la tabla companies existe
                if (!Schema::hasTable('companies')) {
                    return response()->json([
                        'draw' => intval($request->get('draw')),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                        'error' => 'La tabla companies no existe. Ejecute el script SQL primero.'
                    ]);
                }

                $companies = Company::query();

                return DataTables::of($companies)
                    ->addColumn('status', function ($company) {
                        $badge = $company->is_active ?
                            '<span class="badge badge-success">Activa</span>' :
                            '<span class="badge badge-danger">Inactiva</span>';

                        if ($company->isLicenseExpiringSoon()) {
                            $badge .= ' <span class="badge badge-warning">Expira Pronto</span>';
                        } elseif (!$company->isLicenseValid()) {
                            $badge .= ' <span class="badge badge-danger">Licencia Expirada</span>';
                        }

                        return $badge;
                    })
                    ->addColumn('license_info', function ($company) {
                        $html = '<strong>Tipo:</strong> ' . ucfirst($company->license_type) . '<br>';

                        if ($company->license_expires_at) {
                            $expires = $company->license_expires_at->format('d/m/Y');
                            $daysRemaining = $company->daysUntilExpiration();

                            if ($daysRemaining !== null) {
                                if ($daysRemaining > 0) {
                                    $html .= "<strong>Expira:</strong> {$expires} ({$daysRemaining} días)";
                                } else {
                                    $html .= '<span class="text-danger"><strong>Expiró:</strong> ' . $expires . '</span>';
                                }
                            }
                        } else {
                            $html .= '<strong>Sin expiración</strong>';
                        }

                        return $html;
                    })
                    ->addColumn('users_count', function ($company) {
                        $count = $company->users()->count();
                        $max = $company->max_users;

                        $percentage = $max > 0 ? ($count / $max) * 100 : 0;

                        // Clases para Bootstrap 4.6
                        if ($percentage > 80) {
                            $barClass = 'progress-bar bg-danger';
                        } elseif ($percentage > 60) {
                            $barClass = 'progress-bar bg-warning';
                        } else {
                            $barClass = 'progress-bar bg-success';
                        }

                        return "
                            <div class='progress mb-0' style='height: 20px;'>
                                <div class='{$barClass}' role='progressbar' style='width: {$percentage}%' aria-valuenow='{$percentage}' aria-valuemin='0' aria-valuemax='100'>
                                    <span class='text-white font-weight-bold'>{$count}/{$max}</span>
                                </div>
                            </div>
                        ";
                    })
                    ->addColumn('actions', function ($company) {
                        return '
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info" onclick="viewCompany(' . $company->id . ')" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="editCompany(' . $company->id . ')" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="renewLicense(' . $company->id . ')" title="Renovar licencia">
                                    <i class="fas fa-calendar-plus"></i>
                                </button>
                            </div>
                        ';
                    })
                    ->rawColumns(['status', 'license_info', 'users_count', 'actions'])
                    ->make(true);

            } catch (\Exception $e) {
                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Error al cargar empresas: ' . $e->getMessage()
                ]);
            }
        }

        return view('admin.companies.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $licenseTypes = ['trial' => 'Trial', 'standard' => 'Standard', 'premium' => 'Premium'];
        $features = ['cotizaciones', 'inventario', 'produccion', 'terceros', 'reportes', 'multiempresa', 'api_access'];

        return view('admin.companies.create', compact('licenseTypes', 'features'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'nit' => 'nullable|string|unique:companies,nit|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->isValidHexColor($value)) {
                    $fail('El color primario debe ser un color hexadecimal válido (ej: #FF0000).');
                }
            }],
            'secondary_color' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->isValidHexColor($value)) {
                    $fail('El color secundario debe ser un color hexadecimal válido (ej: #FF0000).');
                }
            }],
            'license_type' => 'required|in:trial,standard,premium',
            'license_expires_at' => 'nullable|date|after:today',
            'max_users' => 'required|integer|min:1|max:1000',
            'features' => 'array',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();

        // Procesar logo
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies/logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        // Procesar características
        $data['features'] = $request->features ?? [];

        // Configuraciones por defecto
        $data['theme_settings'] = [
            'sidebar_color' => 'dark',
            'navbar_color' => 'white',
            'font_family' => 'Inter'
        ];

        $data['settings'] = [
            'timezone' => 'America/Bogota',
            'currency' => 'COP',
            'date_format' => 'Y-m-d',
            'decimal_places' => 2
        ];

        Company::create($data);

        return redirect()->route('admin.companies.index')
                        ->with('success', 'Empresa creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        $company->load(['users:id,name,email,company_id']);

        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        $licenseTypes = ['trial' => 'Trial', 'standard' => 'Standard', 'premium' => 'Premium'];
        $features = ['cotizaciones', 'inventario', 'produccion', 'terceros', 'reportes', 'multiempresa', 'api_access'];

        return view('admin.companies.edit', compact('company', 'licenseTypes', 'features'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:255|unique:companies,nit,' . $company->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->isValidHexColor($value)) {
                    $fail('El color primario debe ser un color hexadecimal válido (ej: #FF0000).');
                }
            }],
            'secondary_color' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!$this->isValidHexColor($value)) {
                    $fail('El color secundario debe ser un color hexadecimal válido (ej: #FF0000).');
                }
            }],
            'license_type' => 'required|in:trial,standard,premium',
            'license_expires_at' => 'nullable|date|after:today',
            'max_users' => 'required|integer|min:1|max:1000',
            'features' => 'array',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();

        // Procesar logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }

            $logoPath = $request->file('logo')->store('companies/logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        // Procesar características
        $data['features'] = $request->features ?? [];

        $company->update($data);

        return redirect()->route('admin.companies.index')
                        ->with('success', 'Empresa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // No permitir eliminar empresa con usuarios asignados
        if ($company->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una empresa con usuarios asignados.'
            ]);
        }

        // Eliminar logo
        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Empresa eliminada exitosamente.'
        ]);
    }

    /**
     * Renovar licencia de empresa
     */
    public function renewLicense(Request $request, Company $company)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:60',
            'license_type' => 'required|in:trial,standard,premium'
        ]);

        $currentExpiration = $company->license_expires_at ?? Carbon::now();
        $newExpiration = Carbon::parse($currentExpiration)->addMonths($request->months);

        $company->update([
            'license_expires_at' => $newExpiration,
            'license_type' => $request->license_type,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => "Licencia renovada hasta {$newExpiration->format('d/m/Y')}."
        ]);
    }

    /**
     * Alternar estado activo/inactivo
     */
    public function toggleStatus(Company $company)
    {
        $company->update(['is_active' => !$company->is_active]);

        $status = $company->is_active ? 'activada' : 'desactivada';

        return response()->json([
            'success' => true,
            'message' => "Empresa {$status} exitosamente."
        ]);
    }

    /**
     * Validar si un color es un código hexadecimal válido
     *
     * @param string $color
     * @return bool
     */
    private function isValidHexColor($color)
    {
        // Verificar que comience con #
        if (!str_starts_with($color, '#')) {
            return false;
        }

        // Remover el # y verificar que solo contenga caracteres hexadecimales válidos
        $hex = substr($color, 1);

        // Verificar que tenga la longitud correcta (3 o 6 caracteres)
        if (strlen($hex) !== 3 && strlen($hex) !== 6) {
            return false;
        }

        // Verificar que todos los caracteres sean hexadecimales válidos
        return ctype_xdigit($hex);
    }
}
