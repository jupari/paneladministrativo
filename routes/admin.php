<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\admin\ConfigAccountController;
use App\Http\Controllers\Admin\CuentaController;
use App\Http\Controllers\Admin\CuentaMadreController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EstadoController;
use App\Http\Controllers\Admin\OutlookController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CostCenterController;
use App\Http\Controllers\Admin\UsuarioRolesController;
use App\Http\Controllers\Admin\RolesPermisosController;
use App\Http\Controllers\Contratos\Cargos\CargoController;
use App\Http\Controllers\Contratos\Categorias\CategoriaController;
use App\Http\Controllers\Contratos\Empleados\EmpleadoController;
use App\Http\Controllers\Contratos\ContratoController;
use App\Http\Controllers\Contratos\Novedades\NovedadController;
use App\Http\Controllers\Contratos\Novedades\NovedadDetalleController;
use App\Http\Controllers\Contratos\Parametrizacion\ParametrizacionController;
use App\Http\Controllers\Contratos\Plantillas\PlantillaController;
use App\Http\Controllers\Cotizar\CotizarController;
use App\Http\Controllers\Cotizar\CotizacionController;
use App\Http\Controllers\Cotizar\CotizacionConceptoController;
use App\Http\Controllers\Cotizar\CotizacionItemController;
use App\Http\Controllers\Cotizar\ObservacionController;
use App\Http\Controllers\Cotizar\CondicionComercialController;
use App\Http\Controllers\Cotizar\CotizacionSolicitudController;
use App\Http\Controllers\Cotizar\CotizacionProductoController;
use App\Http\Controllers\Cotizar\CotizacionUtilidadController;
use App\Http\Controllers\elementos\ElementoController;
use App\Http\Controllers\elementos\ElementosController;
use App\Http\Controllers\elementos\SubElementoController;
use App\Http\Controllers\Inventario\BodegaController;
use App\Http\Controllers\Inventario\ProductoController;
use App\Http\Controllers\Produccion\FichaTecnicaController;
use App\Http\Controllers\Produccion\FichaTecnicaBocetoController;
use App\Http\Controllers\Produccion\FichaTecnicaMaterialController;
use App\Http\Controllers\Produccion\FichaTecnicaProcesoController;
use App\Http\Controllers\Produccion\ProdOrderController;
use App\Http\Controllers\Produccion\ProdProductionLogController;
use App\Http\Controllers\Produccion\ProdSettlementController;
use App\Http\Controllers\Produccion\ProdOperationController;
use App\Http\Controllers\Produccion\ProdRateController;
use App\Http\Controllers\Produccion\ProdOrderOperationController;
use App\Http\Controllers\ItemPropio\ItemPropioController;
use App\Http\Controllers\Produccion\MaterialController;
use App\Http\Controllers\Inventario\MovimientoController;
use App\Http\Controllers\Inventario\MovimientoDetalleController;
use App\Http\Controllers\Inventario\ProductoPropiedadController;
use App\Http\Controllers\Inventario\SaldoController;
use App\Http\Controllers\Nomina\NominaConceptoController;
use App\Http\Controllers\Nomina\NominaNovedadController;
use App\Http\Controllers\Nomina\NominaPayRunController;
use App\Http\Controllers\Nomina\NominaPayslipController;
use App\Http\Controllers\Nomina\NominaReportController;
use App\Http\Controllers\Produccion\procesos\ProcesoController;
use App\Http\Controllers\Produccion\procesos\ProcesoDetController;
use App\Http\Controllers\Terceros\Clientes\ClienteController;
use App\Http\Controllers\Terceros\Clientes\ContactoClienteController;
use App\Http\Controllers\Terceros\Clientes\SucursalClienteController;
use App\Http\Controllers\Terceros\Proveedores\ProveedorController;
use App\Http\Controllers\Terceros\UbicacionController;
use App\Http\Controllers\Terceros\Vendedores\VendedorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'company.license'])->group(function () {
    Route::get('admin.users.index', [UserController::class, 'indexDataTable'])->name('admin.users.index');
    Route::post('admin.users.store', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('admin.users.edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::post('admin.users.update/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('admin.users.changepass/{id}', [UserController::class, 'changePass'])->name('admin.users.changePass');
    Route::get('admin.users.show/{id}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('admin.users.delete/{id}', [UserController::class, 'delete'])->name('admin.users.delete');

    // Gestión de Empresas
    Route::controller(CompanyController::class)->group(function () {
        Route::get('admin.companies.index', 'index')->name('admin.companies.index');
        Route::get('admin.companies.create', 'create')->name('admin.companies.create');
        Route::post('admin.companies.store', 'store')->name('admin.companies.store');
        Route::get('admin.companies.show/{company}', 'show')->name('admin.companies.show');
        Route::get('admin.companies.edit/{company}', 'edit')->name('admin.companies.edit');
        Route::put('admin.companies.update/{company}', 'update')->name('admin.companies.update');
        Route::delete('admin.companies.destroy/{company}', 'destroy')->name('admin.companies.destroy');
        Route::post('admin.companies.renew-license/{company}', 'renewLicense')->name('admin.companies.renew-license');
        Route::post('admin.companies.toggle-status/{company}', 'toggleStatus')->name('admin.companies.toggle-status');
    });

    // Permisos
    Route::controller(RolesPermisosController::class)->group(function () {

        Route::get('admin.permission.index', 'index')->name('admin.permission.index');
        Route::get('admin.permission/{id}', 'edit')->name('admin.permission.edit');
        Route::post('admin.permission', 'store')->name('admin.permission.store');
        Route::put('admin.permission/{id}', 'update')->name('admin.permission.update');
    });
    // Roles
    Route::controller(UsuarioRolesController::class)->group(function () {

        Route::get('admin.roles.index', 'index')->name('admin.roles.index');
        Route::get('admin.roles/{id}', 'edit')->name('admin.roles.edit');
        Route::post('admin.roles', 'store')->name('admin.roles.store');
        Route::put('admin.roles/{id}', 'update')->name('admin.roles.update');
    });

    //Cuentas principales
    Route::controller(CuentaMadreController::class)->group(function () {
        Route::get('admin.cuentappal.index', 'index')->name('admin.cuentappal.index');
        Route::get('admin.cuentappal/{id}', 'edit')->name('admin.cuentappal.edit');
        Route::post('admin.cuentappal', 'store')->name('admin.cuentappal.store');
        Route::put('admin.cuentappal/{id}', 'update')->name('admin.cuentappal.update');
        Route::get('admin.cuentappal.find/{email}', 'getDataEmail')->name('admin.cuentappal.find');
        //subir archivos de excel
        Route::post('admin.cuentappal.uploadfile', 'verifyExcelData')->name('admin.cuentappal.uploadfile');
        Route::post('admin.cuentappal.saveLote', 'insertarCuentasMadre')->name('admin.cuentappal.saveLote');
    });

    //Cuentas
    Route::controller(CuentaController::class)->group(function () {
        Route::get('admin.cuenta.index', 'index')->name('admin.cuenta.index');
        Route::get('admin.cuenta/{id}', 'edit')->name('admin.cuenta.edit');
        Route::post('admin.cuenta', 'store')->name('admin.cuenta.store');
        Route::put('admin.cuenta/{id}', 'update')->name('admin.cuenta.update');
        //subir archivos de excel
        Route::post('admin.cuenta.uploadfile', 'verifyExcelData')->name('admin.cuenta.uploadfile');
        Route::post('admin.cuenta.saveLote', 'insertarCuentas')->name('admin.cuenta.saveLote');
    });

    //Cuentas
    Route::controller(EstadoController::class)->group(function () {
        Route::get('admin.estado.index', 'index')->name('admin.estado.index');
        Route::get('admin.estado/{id}', 'edit')->name('admin.estado.edit');
        Route::post('admin.estado', 'store')->name('admin.estado.store');
        Route::put('admin.estado/{id}', 'update')->name('admin.estado.update');
    });

    //Emails
    Route::controller(OutlookController::class)->group(function () {
        Route::get('auth/redirect/{email}', [OutlookController::class, 'redirectToProvider'])->name('auth/redirect');
        Route::get('admin.emails.index', [OutlookController::class, 'redirectToProvider'])->name('admin.emails.index');
        Route::get('admin.emails.getemail/{accountId}/{tipo}', [OutlookController::class, 'getEmails'])->name('admin.emails.getemail');
        Route::get('admin.emails.getemailcorreo/{email}', [OutlookController::class, 'getEmailsCorreo'])->name('admin.emails.getemailcorreo');
        Route::get('callback', [OutlookController::class, 'handleProviderCallback'])->name('callback');

        Route::get('/fetchEmail/{email}/{password}', [OutlookController::class, 'fetchEmails']);
    });



    //Configuracion de tokens de correos
    Route::controller(ConfigAccountController::class)->group(function () {
        Route::get('admin.configemail.index', [ConfigAccountController::class, 'index'])->name('admin.configemail.index');
    });

    Route::controller(ClienteController::class)->group(function () {
        Route::get('admin.clientes.index', [ClienteController::class, 'index'])->name('admin.clientes.index');
        Route::post('admin.clientes.store', [ClienteController::class, 'store'])->name('admin.clientes.store');
        Route::get('admin.clientes.edit/{id}', [ClienteController::class, 'edit'])->name('admin.clientes.edit');
        Route::post('admin.clientes.update/{id}', [ClienteController::class, 'update'])->name('admin.clientes.update');
        Route::delete('admin.clientes.destroy/{id}', [ClienteController::class, 'destroy'])->name('admin.clientes.destroy');
    });

    // Route::controller(ProveedorController::class)->group(function () {
    //     Route::get('admin.proveedores.index', [ProveedorController::class, 'index'])->name('admin.proveedores.index');
    //     Route::post('admin.proveedores.store', [ProveedorController::class, 'store'])->name('admin.proveedores.store');
    //     Route::get('admin.proveedores.edit/{id}', [ProveedorController::class, 'edit'])->name('admin.proveedores.edit');
    //     Route::post('admin.proveedores.update/{id}', [ProveedorController::class, 'update'])->name('admin.proveedores.update');
    //     Route::delete('admin.proveedores.destroy/{id}', [ProveedorController::class, 'destroy'])->name('admin.proveedores.destroy');
    // });

    Route::controller(EmpleadoController::class)->group(function () {
        Route::get('admin.empleados-terceros.index', [EmpleadoController::class, 'index'])->name('admin.empleados-terceros.index');
        Route::post('admin.empleados-terceros.store', [EmpleadoController::class, 'store'])->name('admin.empleados-terceros.store');
        Route::get('admin.empleados-terceros.edit/{id}', [EmpleadoController::class, 'edit'])->name('admin.empleados-terceros.edit');
        Route::post('admin.empleados-terceros.update/{id}', [EmpleadoController::class, 'update'])->name('admin.empleados-terceros.update');
        Route::delete('admin.empleados-terceros.destroy/{id}', [EmpleadoController::class, 'destroy'])->name('admin.empleados-terceros.destroy');
        Route::get('admin.empleados.list', [EmpleadoController::class, 'list'])->name('admin.empleados.list');
    });

    Route::controller(SucursalClienteController::class)->group(function () {
        Route::get('admin.sucursales.index/{id}', [SucursalClienteController::class, 'index'])->name('admin.sucursales.index');
        Route::get('admin.sucursales.getSucursales/{clienteId}', [SucursalClienteController::class, 'getSucursales'])->name('admin.sucursales.getSucursales');
        Route::post('admin.sucursales.store', [SucursalClienteController::class, 'store'])->name('admin.sucursales.store');
        Route::get('admin.sucursales.edit/{id}', [SucursalClienteController::class, 'edit'])->name('admin.sucursales.edit');
        Route::post('admin.sucursales.update/{id}', [SucursalClienteController::class, 'update'])->name('admin.sucursales.update');
        Route::delete('admin.sucursales.destroy/{id}', [SucursalClienteController::class, 'destroy'])->name('admin.sucursales.destroy');
    });

    Route::controller(ContactoClienteController::class)->group(function () {
        Route::get('admin.contactos.index/{id}', [ContactoClienteController::class, 'index'])->name('admin.contactos.index');
        Route::post('admin.contactos.store', [ContactoClienteController::class, 'store'])->name('admin.contactos.store');
        Route::get('admin.contactos.edit/{id}', [ContactoClienteController::class, 'edit'])->name('admin.contactos.edit');
        Route::post('admin.contactos.update/{id}', [ContactoClienteController::class, 'update'])->name('admin.contactos.update');
        Route::delete('admin.contactos.destroy/{id}', [ContactoClienteController::class, 'destroy'])->name('admin.contactos.destroy');
    });


    Route::controller(VendedorController::class)->group(function () {
        Route::get('admin.vendedores.index', [VendedorController::class, 'index'])->name('admin.vendedores.index');
        Route::post('admin.vendedores.store', [VendedorController::class, 'store'])->name('admin.vendedores.store');
        Route::get('admin.vendedores.edit/{id}', [VendedorController::class, 'edit'])->name('admin.vendedores.edit');
        Route::post('admin.vendedores.update/{id}', [VendedorController::class, 'update'])->name('admin.vendedores.update');
        Route::delete('admin.vendedores.destroy/{id}', [VendedorController::class, 'destroy'])->name('admin.vendedores.destroy');
    });

    Route::controller(CargoController::class)->group(function () {
        Route::get('admin.cargos.index', [CargoController::class, 'index'])->name('admin.cargos.index');
        Route::post('admin.cargos.store', [CargoController::class, 'store'])->name('admin.cargos.store');
        Route::get('admin.cargos.edit/{id}', [CargoController::class, 'edit'])->name('admin.cargos.edit');
        Route::post('admin.cargos.update/{id}', [CargoController::class, 'update'])->name('admin.cargos.update');
        Route::delete('admin.cargos.destroy/{id}', [CargoController::class, 'destroy'])->name('admin.cargos.destroy');
    });

    Route::controller(EmpleadoController::class)->group(function () {
        Route::get('admin.empleados.index', [EmpleadoController::class, 'index'])->name('admin.empleados.index');
        Route::post('admin.empleados.store', [EmpleadoController::class, 'store'])->name('admin.empleados.store');
        Route::get('admin.empleados.edit/{id}', [EmpleadoController::class, 'edit'])->name('admin.empleados.edit');
        Route::post('admin.empleados.update/{id}', [EmpleadoController::class, 'update'])->name('admin.empleados.update');
        Route::delete('admin.empleados.destroy/{id}', [EmpleadoController::class, 'destroy'])->name('admin.empleados.destroy');
    });

    Route::controller(ContratoController::class)->group(function () {
        Route::get('admin.contratos.index', [ContratoController::class, 'index'])->name('admin.contratos.index');
        Route::post('admin.contratos.store', [ContratoController::class, 'store'])->name('admin.contratos.store');
        Route::get('admin.contratos.edit/{id}', [ContratoController::class, 'edit'])->name('admin.contratos.edit');
        Route::post('admin.contratos.update/{id}', [ContratoController::class, 'update'])->name('admin.contratos.update');
        Route::delete('admin.contratos.destroy/{id}', [ContratoController::class, 'destroy'])->name('admin.contratos.destroy');
    });

    Route::controller(PlantillaController::class)->group(function () {
        Route::get('admin.plantillas.index', [PlantillaController::class, 'index'])->name('admin.plantillas.index');
        Route::post('admin.plantillas.store', [PlantillaController::class, 'store'])->name('admin.plantillas.store');
        Route::get('admin.plantillas.edit/{id}', [PlantillaController::class, 'edit'])->name('admin.plantillas.edit');
        Route::post('admin.plantillas.update/{id}', [PlantillaController::class, 'update'])->name('admin.plantillas.update');
        Route::delete('admin.plantillas.destroy/{id}', [PlantillaController::class, 'destroy'])->name('admin.plantillas.destroy');
        Route::get('admin.plantillas.getPlaceHolders/{plantillaId}', [PlantillaController::class, 'getPlaceHolders'])->name('admin.plantillas.getPlaceHolders');
        Route::post('admin.plantillas.saveMapping/{plantillaId}', [PlantillaController::class, 'saveMapping'])->name('admin.plantillas.saveMapping');
        Route::post('admin.plantillas.generarContratos', [PlantillaController::class, 'generarContratos'])->name('admin.plantillas.generarContratos');
        Route::post('admin.plantillas.generateDocument/{plantillaId}', [PlantillaController::class, 'generateDocument'])->name('admin.plantillas.generateDocument');
    });

    Route::controller(ParametrizacionController::class)->group(function () {
        Route::get('admin.parametrizacion.index', [ParametrizacionController::class, 'index'])->name('admin.parametrizacion.index');
        Route::post('admin.parametrizacion.storenovedades', [ParametrizacionController::class, 'storeNovedades'])->name('admin.parametrizacion.storenovedades');
        Route::post('admin.parametrizacion.storecostos', [ParametrizacionController::class, 'storeCostos'])->name('admin.parametrizacion.storecostos');
    });

    Route::controller(CategoriaController::class)->group(function () {
        Route::get('admin.categoria.index', [CategoriaController::class, 'index'])->name('admin.categoria.index');
        Route::post('admin.categoria.store', [CategoriaController::class, 'store'])->name('admin.categoria.store');
        Route::get('admin.categoria.edit/{id}', [CategoriaController::class, 'edit'])->name('admin.categoria.edit');
        Route::post('admin.categoria.update/{id}', [CategoriaController::class, 'update'])->name('admin.categoria.update');
    });

    Route::controller(NovedadController::class)->group(function () {
        Route::get('admin.novedad.index', [NovedadController::class, 'index'])->name('admin.novedad.index');
        Route::post('admin.novedad.store', [NovedadController::class, 'store'])->name('admin.novedad.store');
        Route::get('admin.novedad.create', [NovedadController::class, 'create'])->name('admin.novedad.create');
        Route::get('admin.novedad.edit/{id}', [NovedadController::class, 'edit'])->name('admin.novedad.edit');
        Route::put('admin.novedad.update/{id}', [NovedadController::class, 'update'])->name('admin.novedad.update');
    });

    Route::controller(NovedadDetalleController::class)->group(function () {
        Route::get('admin.novedaddetalle.getByNovedad/{id}', [NovedadDetalleController::class, 'getByNovedad'])->name('admin.novedaddetalle.getByNovedad');
    });

    Route::controller(UbicacionController::class)->group(function () {
        Route::get('admin.ubicaciones.index', [UbicacionController::class, 'index'])->name('admin.ubicaciones.index');
        Route::post('admin.ubicaciones.store', [UbicacionController::class, 'store'])->name('admin.ubicaciones.store');
        Route::get('admin.ubicaciones.edit/{id}', [UbicacionController::class, 'edit'])->name('admin.ubicaciones.edit');
        Route::post('admin.ubicaciones.update/{id}', [UbicacionController::class, 'update'])->name('admin.ubicaciones.update');
        Route::delete('admin.ubicaciones/{id}/{tipo}', [UbicacionController::class, 'destroy'])->name('admin.ubicaciones.destroy');
    });

    Route::controller(CotizarController::class)->group(function () {
        Route::get('admin.cotizar.index', [CotizarController::class, 'index'])->name('admin.cotizar.index');
        Route::get('admin.cotizar.create', [CotizarController::class, 'create'])->name('admin.cotizar.create');
    });

    // Rutas para Cotizaciones CRUD
    Route::controller(CotizacionController::class)->group(function () {
        Route::get('admin.cotizaciones.index', 'index')->name('admin.cotizaciones.index');
        Route::get('admin.cotizaciones.create', 'create')->name('admin.cotizaciones.create');
        Route::post('admin.cotizaciones.store', 'store')->name('admin.cotizaciones.store');
        Route::get('admin.cotizaciones.show/{id}', 'show')->name('admin.cotizaciones.show');
        Route::get('admin.cotizaciones.edit/{id}', 'edit')->name('admin.cotizaciones.edit');
        Route::put('admin.cotizaciones.update/{id}', 'update')->name('admin.cotizaciones.update');
        Route::delete('admin.cotizaciones.destroy/{id}', 'destroy')->name('admin.cotizaciones.destroy');

        // Rutas para PDF
        Route::get('admin.cotizaciones.pdf/{id}', 'generatePdf')->name('admin.cotizaciones.pdf');
        Route::get('admin.cotizaciones.preview/{id}', 'previewPdf')->name('admin.cotizaciones.preview');

        // Rutas adicionales para AJAX
        Route::get('admin.cotizaciones.getSucursales/{terceroId}', 'getSucursales')->name('admin.cotizaciones.getSucursales');
        Route::get('admin.cotizaciones.getContactos/{terceroId}', 'getContactos')->name('admin.cotizaciones.getContactos');
        Route::get('admin.cotizaciones.generateNextNumber', 'generateNextNumber')->name('admin.cotizaciones.generateNextNumber');
        Route::post('admin.cotizaciones.duplicate/{id}', 'duplicate')->name('admin.cotizaciones.duplicate');
        Route::post('admin.cotizaciones.search', 'search')->name('admin.cotizaciones.search');
        Route::delete('admin.cotizaciones.destroy/{id}', 'destroy')->name('admin.cotizaciones.destroy');
    });

    // Rutas para Productos y Salarios de Cotizaciones
    Route::controller(CotizacionProductoController::class)->group(function () {
        Route::get('admin.cotizaciones.productos.obtener', 'obtenerProductos')->name('admin.cotizaciones.productos.obtener');
        Route::get('admin.cotizaciones.cargos.obtener', 'obtenerCargos')->name('admin.cotizaciones.cargos.obtener');
        Route::get('admin.cotizaciones.categorias.obtener', 'obtenerCategorias')->name('admin.cotizaciones.categorias.obtener');
        Route::post('admin.cotizaciones.items-categoria.obtener', 'obtenerItemsPorCategoria')->name('admin.cotizaciones.items-categoria.obtener');
        Route::post('admin.cotizaciones.valores-defecto.obtener', 'obtenerValoresPorDefecto')->name('admin.cotizaciones.valores-defecto.obtener');
        Route::post('admin.cotizaciones.salarios.guardar', 'guardarSalariosCotizacion')->name('admin.cotizaciones.salarios.guardar');
        Route::post('admin.cotizaciones.salarios.calcular', 'calcularCostoSalarios')->name('admin.cotizaciones.salarios.calcular');
        Route::get('admin.cotizaciones.tipos-costo.obtener', 'obtenerTipoCostos')->name('admin.cotizaciones.tipos-costo.obtener');
        Route::get('admin.cotizaciones/{id}/elementos', 'obtenerElementosCotizacion')->name('admin.cotizaciones.elementos.obtener');
        Route::post('admin.cotizaciones.productos.agregar', 'agregarProductosCotizacion')->name('admin.cotizaciones.productos.agregar');
        Route::delete('admin.cotizaciones.elementos.quitar/{id}', 'quitarElementosCotizacion')->name('admin.cotizaciones.elementos.quitar');

        // Nuevas rutas para gestión avanzada de productos
        Route::put('admin.cotizaciones.productos.actualizar/{id}', 'actualizarProducto')->name('admin.cotizaciones.productos.actualizar');
        Route::delete('admin.cotizaciones.productos.eliminar/{id}', 'eliminarProducto')->name('admin.cotizaciones.productos.eliminar');
        Route::get('cotizaciones/productos/obtener', 'obtenerProductosCotizacion')->name('admin.cotizaciones.productos.obtener.guardados');
        Route::get('cotizaciones/totales/obtener', 'obtenerTotalesCotizacion')->name('admin.cotizaciones.totales.obtener');
        Route::post('admin.cotizaciones.productos.reordenar', 'reordenarProductos')->name('admin.cotizaciones.productos.reordenar');
        Route::post('admin.cotizaciones.productos.duplicar', 'duplicarProductos')->name('admin.cotizaciones.productos.duplicar');
        Route::get('admin.cotizaciones.productos.buscar', 'buscarProductos')->name('admin.cotizaciones.productos.buscar');
        Route::get('admin.cotizaciones/{cotizacionId}/totales', 'obtenerTotales')->name('admin.cotizaciones.totales');
        Route::post('admin.cotizaciones.productos.descuento-global', 'aplicarDescuentoGlobal')->name('admin.cotizaciones.productos.descuento-global');
    });

    // Rutas para Cotizaciones Conceptos
    Route::controller(CotizacionConceptoController::class)->group(function () {
        Route::get('admin.cotizaciones.conceptos.getConceptos', 'getConceptos')->name('admin.cotizaciones.conceptos.getConceptos');
        Route::post('admin.cotizaciones.conceptos.store', 'store')->name('admin.cotizaciones.conceptos.store');
        Route::get('admin.cotizaciones.conceptos.getCotizacionConceptos/{cotizacionId}', 'getCotizacionConceptos')->name('admin.cotizaciones.conceptos.getCotizacionConceptos');
        Route::put('admin.cotizaciones.conceptos.update/{cotizacionId}', 'update')->name('admin.cotizaciones.conceptos.update');
        Route::delete('admin.cotizaciones.conceptos.destroy/{id}', 'destroy')->name('admin.cotizaciones.conceptos.destroy');
        Route::post('admin.cotizaciones.conceptos.calcularTotales', 'calcularTotales')->name('admin.cotizaciones.conceptos.calcularTotales');
    });

    // Rutas para Observaciones de Cotizaciones
    Route::controller(ObservacionController::class)->group(function () {
        Route::get('admin.cotizaciones.observaciones.getObservaciones', 'getObservaciones')->name('admin.cotizaciones.observaciones.getObservaciones');
        Route::get('admin.cotizaciones.observaciones.getCotizacionObservaciones/{cotizacionId}', 'getCotizacionObservaciones')->name('admin.cotizaciones.observaciones.getCotizacionObservaciones');
        Route::post('admin.cotizaciones.observaciones.store', 'store')->name('admin.cotizaciones.observaciones.store');
        Route::put('admin.cotizaciones.observaciones.update/{cotizacionId}', 'update')->name('admin.cotizaciones.observaciones.update');
        Route::delete('admin.cotizaciones.observaciones.destroy/{id}', 'destroy')->name('admin.cotizaciones.observaciones.destroy');
    });

    // Rutas para Condiciones Comerciales de Cotizaciones
    Route::controller(CondicionComercialController::class)->group(function () {
        Route::get('admin.cotizaciones.condiciones.getCotizacionCondiciones/{cotizacionId}', 'getCotizacionCondiciones')->name('admin.cotizaciones.condiciones.getCotizacionCondiciones');
        Route::post('admin.cotizaciones.condiciones.store', 'store')->name('admin.cotizaciones.condiciones.store');
        Route::put('admin.cotizaciones.condiciones.update/{cotizacionId}', 'update')->name('admin.cotizaciones.condiciones.update');
        Route::delete('admin.cotizaciones.condiciones.destroy/{cotizacionId}', 'destroy')->name('admin.cotizaciones.condiciones.destroy');
        Route::post('admin.cotizaciones.condiciones.validar', 'validarCondiciones')->name('admin.cotizaciones.condiciones.validar');
    });

    // Rutas para Items de Cotizaciones
    Route::controller(CotizacionItemController::class)->group(function () {
        Route::get('admin.cotizaciones.items.getCotizacionItems/{cotizacionId}', 'getCotizacionItems')->name('admin.cotizaciones.items.getCotizacionItems');
        Route::get('admin.cotizaciones.items.getSubitems', 'getSubitems')->name('admin.cotizaciones.items.getSubitems');
        Route::get('admin.cotizaciones.items.getItemSubitems/{itemId}', 'getItemSubitems')->name('admin.cotizaciones.items.getItemSubitems');
        Route::get('admin.cotizaciones.items.getSubitem/{subitemId}', 'getSubitem')->name('admin.cotizaciones.items.getSubitem');
        Route::get('admin.cotizaciones.items.getUnidadesMedida', 'getUnidadesMedida')->name('admin.cotizaciones.items.getUnidadesMedida');
        Route::post('admin.cotizaciones.items.store', 'store')->name('admin.cotizaciones.items.store');
        Route::post('admin.cotizaciones.items.createItem', 'createItem')->name('admin.cotizaciones.items.createItem');
        Route::put('admin.cotizaciones.items.update/{cotizacionId}', 'update')->name('admin.cotizaciones.items.update');
        Route::delete('admin.cotizaciones.items.destroy/{id}', 'destroy')->name('admin.cotizaciones.items.destroy');
        Route::post('admin.cotizaciones.items.createSubitem', 'createSubitem')->name('admin.cotizaciones.items.createSubitem');
        Route::put('admin.cotizaciones.items.updateSubitem/{subitemId}', 'updateSubitem')->name('admin.cotizaciones.items.updateSubitem');
        Route::delete('admin.cotizaciones.items.destroySubitem/{subitemId}', 'destroySubitem')->name('admin.cotizaciones.items.destroySubitem');
    });

    Route::controller(CotizacionSolicitudController::class)->group(function () {
        Route::get('admin.cotizaciones.solicitudes.index', 'index')->name('admin.cotizaciones.solicitudes.index');
        Route::delete('admin.cotizaciones.solicitudes.auth/{id}', 'authorizeCotizacion')->name('admin.cotizaciones.solicitudes.auth');
    });

    // Rutas para Utilidades de Cotizaciones
    Route::controller(CotizacionUtilidadController::class)->group(function () {
        Route::post('admin.cotizaciones.utilidades.store', 'store')->name('admin.cotizaciones.utilidades.store');
        Route::delete('admin.cotizaciones.utilidades.destroy/{id}', 'destroy')->name('admin.cotizaciones.utilidades.destroy');
        Route::get('admin.cotizaciones.utilidades.obtener/{cotizacionId}', 'obtenerUtilidades')->name('admin.cotizaciones.utilidades.obtener');
        Route::get('admin.cotizaciones.utilidades.categorias/{cotizacionId}', 'obtenerCategorias')->name('admin.cotizaciones.utilidades.categorias');
        Route::get('admin.cotizaciones.utilidades.items-propios/{cotizacionId}', 'obtenerItemsPropios')->name('admin.cotizaciones.utilidades.items-propios');
        Route::get('admin.cotizaciones.utilidades.items-categoria/{cotizacionId}/{categoriaId}', 'obtenerItemsPorCategoria')->name('admin.cotizaciones.utilidades.items-categoria');
    });

    Route::controller(ItemPropioController::class)->group(function () {
        //Route::post('admin.itempropio.store', [ItemPropioController::class, 'store'])->name('admin.itempropio.store');
        Route::get('admin.items-propios', [ItemPropioController::class, 'index'])->name('admin.items-propios.index');

        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.items-propios/list', [ItemPropioController::class, 'listar'])->name('admin.items-propios.list');
        Route::post('admin.items-propios', [ItemPropioController::class, 'store'])->name('admin.items-propios.store');
        Route::put('admin.items-propios/{item_propio}', [ItemPropioController::class, 'update'])->name('admin.items-propios.update');
        Route::delete('admin.items-propios/{item_propio}', [ItemPropioController::class, 'destroy'])->name('admin.items-propios.destroy');
    });


    Route::controller(FichaTecnicaController::class)->group(function () {
        Route::get('admin.fichas-tecnicas.index', [FichaTecnicaController::class, 'index'])->name('admin.fichas-tecnicas.index');

        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.fichas-tecnicas/list', [FichaTecnicaController::class, 'listar'])->name('admin.fichas-tecnicas.list');
        Route::post('admin.fichas-tecnicas.store', [FichaTecnicaController::class, 'store'])->name('admin.fichas-tecnicas.store');
        Route::get('admin.fichas-tecnicas.create', [FichaTecnicaController::class, 'create'])->name('admin.fichas-tecnicas.create');
        Route::get('admin.fichas-tecnicas.show/{id}', [FichaTecnicaController::class, 'show'])->name('admin.fichas-tecnicas.show');
        Route::get('admin.fichas-tecnicas.getDataById/{id}', [FichaTecnicaController::class, 'getDataById'])->name('admin.fichas-tecnicas.getDataById');
        Route::put('admin.fichas-tecnicas/{fichaTecnica_id}', [FichaTecnicaController::class, 'update'])->name('admin.fichas-tecnicas.update');
        Route::get('admin.fichas-tecnicas.change/{ficha_tecnica}', [FichaTecnicaController::class, 'destroy'])->name('admin.fichas-tecnicas.destroy');
    });

    Route::controller(FichaTecnicaBocetoController::class)->group(function () {
        Route::get('admin.fichas-tecnicas-bocetos.index', [FichaTecnicaBocetoController::class, 'index'])->name('admin.fichas-tecnicas-bocetos.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.fichas-tecnicas-bocetos/list', [FichaTecnicaBocetoController::class, 'listar'])->name('admin.fichas-tecnicas-bocetos.list');
        Route::post('admin.fichas-tecnicas-bocetos.store', [FichaTecnicaBocetoController::class, 'store'])->name('admin.fichas-tecnicas-bocetos.store');
        Route::get('admin.fichas-tecnicas-bocetos.create', [FichaTecnicaBocetoController::class, 'create'])->name('admin.fichas-tecnicas-bocetos.create');
        Route::put('admin.fichas-tecnicas-bocetos/{ficha_tecnica_boceto}', [FichaTecnicaBocetoController::class, 'update'])->name('admin.fichas-tecnicas-bocetos.update');
        Route::delete('admin.fichas-tecnicas-bocetos/{ficha_tecnica_boceto}', [FichaTecnicaBocetoController::class, 'destroy'])->name('admin.fichas-tecnicas-bocetos.destroy');
    });


    Route::controller(FichaTecnicaMaterialController::class)->group(function () {
        Route::get('admin.fichas-tecnicas-materiales.index/{id}', [FichaTecnicaMaterialController::class, 'index'])->name('admin.fichas-tecnicas-materiales.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.fichas-tecnicas-materiales/list', [FichaTecnicaMaterialController::class, 'listar'])->name('admin.fichas-tecnicas-materiales.list');
        Route::post('admin.fichas-tecnicas-materiales.store', [FichaTecnicaMaterialController::class, 'store'])->name('admin.fichas-tecnicas-materiales.store');
        Route::get('admin.fichas-tecnicas-materiales.create', [FichaTecnicaMaterialController::class, 'create'])->name('admin.fichas-tecnicas-materiales.create');
        Route::post('admin.fichas-tecnicas-materiales/{ficha_tecnica_materiale}', [FichaTecnicaMaterialController::class, 'update'])->name('admin.fichas-tecnicas-materiales.update');
        Route::delete('admin.fichas-tecnicas-materiales/{ficha_tecnica_materiale}', [FichaTecnicaMaterialController::class, 'destroy'])->name('admin.fichas-tecnicas-materiales.destroy');
    });

    Route::controller(FichaTecnicaProcesoController::class)->group(function () {
        Route::get('admin.fichas-tecnicas-procesos.index/{id}', [FichaTecnicaProcesoController::class, 'index'])->name('admin.fichas-tecnicas-procesos.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.fichas-tecnicas-procesos/list', [FichaTecnicaProcesoController::class, 'listar'])->name('admin.fichas-tecnicas-procesos.list');
        Route::post('admin.fichas-tecnicas-procesos.store', [FichaTecnicaProcesoController::class, 'store'])->name('admin.fichas-tecnicas-procesos.store');
        Route::get('admin.fichas-tecnicas-procesos.create', [FichaTecnicaProcesoController::class, 'create'])->name('admin.fichas-tecnicas-procesos.create');
        Route::post('admin.fichas-tecnicas-procesos/{ficha_tecnica_proceso}', [FichaTecnicaProcesoController::class, 'update'])->name('admin.fichas-tecnicas-procesos.update');
        Route::delete('admin.fichas-tecnicas-procesos/{ficha_tecnica_proceso}', [FichaTecnicaProcesoController::class, 'destroy'])->name('admin.fichas-tecnicas-procesos.destroy');
    });


    Route::controller(MaterialController::class)->group(function () {
        Route::get('admin.materiales.index', [MaterialController::class, 'index'])->name('admin.materiales.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.materiales/list', [MaterialController::class, 'listar'])->name('admin.materiales.list');
        Route::post('admin.materiales.store', [MaterialController::class, 'store'])->name('admin.materiales.store');
        Route::get('admin.materiales.create', [MaterialController::class, 'create'])->name('admin.materiales.create');
        Route::get('admin.materiales.edit/{id}', [MaterialController::class, 'edit'])->name('admin.materiales.edit');
        Route::put('admin.materiales/{id}', [MaterialController::class, 'update'])->name('admin.materiales.update');
        Route::delete('admin.materiales/{material}', [MaterialController::class, 'destroy'])->name('admin.materiales.destroy');
    });

    Route::controller(ElementoController::class)->group(function () {
        Route::get('admin.elementos.index', [ElementoController::class, 'index'])->name('admin.elementos.index');
        Route::get('admin.elementos.show/{id}', [ElementoController::class, 'show'])->name('admin.elementos.show');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.elementos/list/{codigo}', [ElementoController::class, 'listar'])->name('admin.elementos.list');
        Route::post('admin.elementos.store', [ElementoController::class, 'store'])->name('admin.elementos.store');
        Route::get('admin.elementos.create', [ElementoController::class, 'create'])->name('admin.elementos.create');
        Route::put('admin.elementos/{elemento}', [ElementoController::class, 'update'])->name('admin.elementos.update');
        Route::delete('admin.elementos.destroy/{elemento}', [ElementoController::class, 'destroy'])->name('admin.elementos.destroy');
        Route::get('admin.elementos.codigo/{codigo}', [ElementoController::class, 'listarxCodigo'])->name('admin.elementos.codigo');
    });

    Route::controller(SubElementoController::class)->group(function () {
        Route::get('admin.subelementos.index/{codigo}', [SubElementoController::class, 'index'])->name('admin.subelementos.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.subelementos/list/{codigo}', [SubElementoController::class, 'listar'])->name('admin.subelementos.list');
        Route::post('admin.subelementos.store', [SubElementoController::class, 'store'])->name('admin.subelementos.store');
        Route::get('admin.subelementos.create', [SubElementoController::class, 'create'])->name('admin.subelementos.create');
        Route::put('admin.subelementos/{subelemento}', [SubElementoController::class, 'update'])->name('admin.subelementos.update');
        Route::delete('admin.subelementos.destroy/{subelemento}', [SubElementoController::class, 'destroy'])->name('admin.subelementos.destroy');
    });

    Route::controller(ProcesoController::class)->group(function () {
        Route::get('admin.procesos.index', [ProcesoController::class, 'index'])->name('admin.procesos.index');
        Route::get('admin.procesos.show/{id}', [ProcesoController::class, 'show'])->name('admin.procesos.show');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.procesos/list', [ProcesoController::class, 'listar'])->name('admin.procesos.list');
        Route::post('admin.procesos.store', [ProcesoController::class, 'store'])->name('admin.procesos.store');
        Route::get('admin.procesos.create', [ProcesoController::class, 'create'])->name('admin.procesos.create');
        Route::put('admin.procesos/{proceso}', [ProcesoController::class, 'update'])->name('admin.procesos.update');
        Route::delete('admin.procesos.destroy/{proceso}', [ProcesoController::class, 'destroy'])->name('admin.procesos.destroy');
        Route::get('admin.procesos.codigo/{codigo}', [ProcesoController::class, 'listarxCodigo'])->name('admin.procesos.codigo');
    });

    Route::controller(ProcesoDetController::class)->group(function () {
        Route::get('admin.procesosdet.index/{codigo}', [ProcesoDetController::class, 'index'])->name('admin.procesosdet.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.procesosdet/list/{codigo}', [ProcesoDetController::class, 'listar'])->name('admin.procesosdet.list');
        Route::post('admin.procesosdet.store', [ProcesoDetController::class, 'store'])->name('admin.procesosdet.store');
        Route::get('admin.procesosdet.create', [ProcesoDetController::class, 'create'])->name('admin.procesosdet.create');
        Route::put('admin.procesosdet/{proceso}', [ProcesoDetController::class, 'update'])->name('admin.procesosdet.update');
        Route::delete('admin.procesosdet.destroy/{proceso}', [ProcesoDetController::class, 'destroy'])->name('admin.procesosdet.destroy');
    });

    //   Rutas de Inventario.
    Route::controller(ProductoController::class)->group(function () {
        Route::get('admin.productos.index', [ProductoController::class, 'index'])->name('admin.productos.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.productos/list', [ProductoController::class, 'listar'])->name('admin.productos.list');
        Route::post('admin.productos.store', [ProductoController::class, 'store'])->name('admin.productos.store');
        Route::get('admin.productos.create', [ProductoController::class, 'create'])->name('admin.productos.create');
        Route::get('admin.productos.show/{id}', [ProductoController::class, 'edit'])->name('admin.productos.show');
        Route::put('admin.productos/{producto}', [ProductoController::class, 'update'])->name('admin.productos.update');
        Route::delete('admin.productos.destroy/{producto}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy');
    });

    Route::controller(ProductoPropiedadController::class)->group(function () {
        Route::get('admin.productospropiedades.index/{productoId}', [ProductoPropiedadController::class, 'index'])->name('admin.productospropiedades.index');
        Route::post('admin.productospropiedades.store', [ProductoPropiedadController::class, 'store'])->name('admin.productospropiedades.store');
        Route::delete('admin.productospropiedades.destroy/{id}', [ProductoPropiedadController::class, 'destroy'])->name('admin.productospropiedades.destroy');
    });


    Route::controller(BodegaController::class)->group(function () {
        Route::get('admin.bodegas.index', [BodegaController::class, 'index'])->name('admin.bodegas.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.bodegas/list', [BodegaController::class, 'listar'])->name('admin.bodegas.list');
        Route::post('admin.bodegas.store', [BodegaController::class, 'store'])->name('admin.bodegas.store');
        Route::get('admin.bodegas.create', [BodegaController::class, 'create'])->name('admin.bodegas.create');
        Route::get('admin.bodegas.edit/{id}', [BodegaController::class, 'edit'])->name('admin.bodegas.edit');
        Route::put('admin.bodegas.update/{bodega}', [BodegaController::class, 'update'])->name('admin.bodegas.update');
        Route::delete('admin.bodegas.destroy/{bodega}', [BodegaController::class, 'destroy'])->name('admin.bodegas.destroy');
    });

    Route::controller(MovimientoController::class)->group(function () {
        Route::get('admin.movimientos.index', [MovimientoController::class, 'index'])->name('admin.movimientos.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.movimientos/list', [MovimientoController::class, 'listar'])->name('admin.movimientos.list');
        Route::post('admin.movimientos.store', [MovimientoController::class, 'store'])->name('admin.movimientos.store');
        Route::get('admin.movimientos.edit/{id}', [MovimientoController::class, 'edit'])->name('admin.movimientos.edit');
        Route::get('admin.movimientos.create', [MovimientoController::class, 'create'])->name('admin.movimientos.create');
        Route::put('admin.movimientos.update/{movimiento}', [MovimientoController::class, 'update'])->name('admin.movimientos.update');
        Route::delete('admin.movimientos.destroy/{movimiento}', [MovimientoController::class, 'destroy'])->name('admin.movimientos.destroy');
    });

    Route::controller(MovimientoDetalleController::class)->group(function () {
        Route::get('admin.movimientosdetalles.index/{numeroDocumento}', [MovimientoDetalleController::class, 'index'])->name('admin.movimientosdetalles.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.movimientosdetalles/list', [MovimientoDetalleController::class, 'listar'])->name('admin.movimientosdetalles.list');
        Route::post('admin.movimientosdetalles.store', [MovimientoDetalleController::class, 'store'])->name('admin.movimientosdetalles.store');
        Route::get('admin.movimientosdetalles.edit/{id}', [MovimientoDetalleController::class, 'edit'])->name('admin.movimientosdetalles.edit');
        Route::get('admin.movimientosdetalles.create', [MovimientoDetalleController::class, 'create'])->name('admin.movimientosdetalles.create');
        Route::put('admin.movimientosdetalles.update/{movimiento}', [MovimientoDetalleController::class, 'update'])->name('admin.movimientosdetalles.update');
        Route::delete('admin.movimientosdetalles.destroy/{movimiento}', [MovimientoDetalleController::class, 'destroy'])->name('admin.movimientosdetalles.destroy');
    });

    Route::controller(SaldoController::class)->group(function () {
        Route::get('admin.saldos.index', [SaldoController::class, 'index'])->name('admin.saldos.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.saldos/list/{product_id}/{bodega_id}', [SaldoController::class, 'listar'])->name('admin.saldos.list');
        Route::post('admin.saldos.store', [SaldoController::class, 'store'])->name('admin.saldos.store');
        Route::put('admin.saldos/{saldo}', [SaldoController::class, 'update'])->name('admin.saldos.update');
        Route::delete('admin.saldos.destroy/{saldo}', [SaldoController::class, 'destroy'])->name('admin.saldos.destroy');
    });

    Route::controller(ProveedorController::class)->group(function () {
        Route::get('admin.proveedores.index', [ProveedorController::class, 'index'])->name('admin.proveedores.index');
        // Endpoints JSON para la grilla (Tabulator)
        Route::get('admin.proveedores/list', [ProveedorController::class, 'listar'])->name('admin.proveedores.list');
        Route::post('admin.proveedores.store', [ProveedorController::class, 'store'])->name('admin.proveedores.store');
        Route::get('admin.proveedores.edit/{id}', [ProveedorController::class, 'edit'])->name('admin.proveedores.edit');
        Route::get('admin.proveedores.create', [ProveedorController::class, 'create'])->name('admin.proveedores.create');
        Route::put('admin.proveedores.update/{proveedor}', [ProveedorController::class, 'update'])->name('admin.proveedores.update');
        Route::delete('admin.proveedores.destroy/{proveedor}', [ProveedorController::class, 'destroy'])->name('admin.proveedores.destroy');
    });


    Route::controller(NominaPayRunController::class)->group(function () {
        Route::get('admin.nomina.payruns.index', [NominaPayRunController::class, 'index'])->name('admin.nomina.payruns.index');
        Route::post('admin.nomina.payruns.store', [NominaPayRunController::class, 'store'])->name('admin.nomina.payruns.store');
        Route::get('admin.nomina.payruns.show/{id}', [NominaPayRunController::class, 'show'])->name('admin.nomina.payruns.show');
        Route::post('admin.nomina.payruns.update/{id}', [NominaPayRunController::class, 'update'])->name('admin.nomina.payruns.update');
        Route::post('admin.nomina.payruns.calculate/{id}', [NominaPayRunController::class, 'calculate'])->name('admin.nomina.payruns.calculate');
        Route::get('admin.nomina.payruns.list', [NominaPayRunController::class, 'list'])->name('admin.nomina.payruns.list');
    });


    Route::controller(NominaNovedadController::class)->group(function () {
        Route::get('admin.nomina.novelties.index', [NominaNovedadController::class, 'index'])->name('admin.nomina.novelties.index');
        Route::get('admin.nomina.novelties.participants', [NominaNovedadController::class, 'participants'])->name('admin.nomina.novelties.participants');
        Route::post('admin.nomina.novelties.store', [NominaNovedadController::class, 'store'])->name('admin.nomina.novelties.store');
        Route::get('admin.nomina.novelties.edit/{id}', [NominaNovedadController::class, 'edit'])->name('admin.nomina.novelties.edit');
        Route::post('admin.nomina.novelties.update/{id}', [NominaNovedadController::class, 'update'])->name('admin.nomina.novelties.update');
        Route::post('admin.nomina.novelties.recalculate-destajo', [NominaNovedadController::class, 'recalculateDestajo'])
                ->name('admin.nomina.novelties.recalculateDestajo');
        Route::post('admin.nomina.novelties.recalculate-destajo-settlements', [NominaNovedadController::class, 'recalculateDestajoFromSettlements'])
                ->name('admin.nomina.novelties.recalculateDestajoFromSettlements');
        Route::post('admin.nomina.novelties.duplicate/{id}', [NominaNovedadController::class, 'duplicate'])
                ->name('admin.nomina.novelties.duplicate');
    });

    Route::controller(NominaConceptoController::class)->group(function () {
        Route::get('admin.nomina.concepts.index', [NominaConceptoController::class, 'index'])->name('admin.nomina.concepts.index');
        Route::post('admin.nomina.concepts.store', [NominaConceptoController::class, 'store'])->name('admin.nomina.concepts.store');
        Route::get('admin.nomina.concepts.edit/{id}', [NominaConceptoController::class, 'edit'])->name('admin.nomina.concepts.edit');
        Route::post('admin.nomina.concepts.update/{id}', [NominaConceptoController::class, 'update'])->name('admin.nomina.concepts.update');
        Route::get('admin.nomina.concepts.list', [NominaConceptoController::class, 'list'])->name('admin.nomina.concepts.list');
    });

    // routes/web.php (o routes/admin.php)

    Route::controller(NominaReportController::class)->group(function () {
        Route::get('admin.nomina.reports.participants.index', [NominaReportController::class, 'index'])->name('admin.nomina.reports.participants.index');
        Route::get('admin.nomina.reports.payruns.list', [NominaReportController::class, 'payRunsList'])->name('admin.nomina.reports.payruns.list'); // select
        Route::get('admin.nomina.reports.lines', [NominaReportController::class, 'lines'])->name('admin.nomina.reports.lines');
    });

    Route::controller(NominaPayslipController::class)->group(function () {
        Route::get('admin.nomina.payruns.payslips', [NominaPayslipController::class, 'index'])->name('admin.nomina.payruns.payslips.index');
        Route::get('admin.nomina.payruns.payslips.list/{payRunId}', [NominaPayslipController::class, 'list'])->name('admin.nomina.payruns.payslips.list'); // select
        Route::get('admin.nomina.payruns.payslips.show/{payRun}/{participantType}/{participantId}', [NominaPayslipController::class, 'show'])->name('admin.nomina.payruns.payslips.show');
    });


    // Órdenes
    Route::controller(ProdOrderController::class)->group(function () {
        Route::get('admin.produccion.orders.index', [ProdOrderController::class, 'index'])->name('admin.produccion.orders.index');
        Route::post('admin.produccion.orders.store', [ProdOrderController::class, 'store'])->name('admin.produccion.orders.store');
        Route::get('admin.produccion.orders.edit/{id}', [ProdOrderController::class, 'edit'])->name('admin.produccion.orders.edit');
        Route::post('admin.produccion.orders.update/{id}', [ProdOrderController::class, 'update'])->name('admin.produccion.orders.update');
    });

    // Logs
    Route::controller(ProdProductionLogController::class)->group(function () {
        Route::get('admin.produccion.logs.index', [ProdProductionLogController::class, 'index'])->name('admin.produccion.logs.index');
        Route::post('admin.produccion.logs.store', [ProdProductionLogController::class, 'store'])->name('admin.produccion.logs.store');
        Route::get('admin.produccion.logs.edit/{id}', [ProdProductionLogController::class, 'edit'])->name('admin.produccion.logs.edit');
        Route::post('admin.produccion.logs.update/{id}', [ProdProductionLogController::class, 'update'])->name('admin.produccion.logs.update');
        Route::get('admin.produccion.employees.list', [ProdProductionLogController::class, 'employeesList'])->name('admin.produccion.employees.list');
    });

    // Liquidación
    Route::controller(ProdSettlementController::class)->group(function () {
        Route::get('admin.produccion.settlements.index', [ProdSettlementController::class, 'index'])->name('admin.produccion.settlements.index');
        Route::post('admin.produccion.settlements.calculate/{orderId}', [ProdSettlementController::class, 'calculate'])->name('admin.produccion.settlements.calculate');
        Route::post('admin.produccion.settlements.send_to_nomina/{orderId}', [ProdSettlementController::class, 'sendToNomina'])->name('admin.produccion.settlements.send_to_nomina');
        Route::get('admin.produccion.orders.list', [ProdSettlementController::class, 'ordersList'])->name('admin.produccion.orders.list');
    });

    // Helpers para selects
    Route::controller(ProdOrderController::class)->group(function () {
        Route::get('admin.produccion.orders', [ProdOrderController::class, 'index'])->name('admin.produccion.orders.index');
        Route::get('admin.produccion.products.list', [ProdOrderController::class, 'productsList'])->name('admin.produccion.products.list');
        Route::get('admin.produccion.operations.list', [ProdOrderController::class, 'operationsList'])->name('admin.produccion.operations.list');
    });

    // CRUD Operaciones
    Route::controller(ProdOperationController::class)->group(function () {
        Route::get('admin.produccion.operations.index', [ProdOperationController::class, 'index'])->name('admin.produccion.operations.index');
        Route::post('admin.produccion.operations.store', [ProdOperationController::class, 'store'])->name('admin.produccion.operations.store');
        Route::get('admin.produccion.operations.edit/{id}', [ProdOperationController::class, 'edit'])->name('admin.produccion.operations.edit');
        Route::post('admin.produccion.operations.update/{id}', [ProdOperationController::class, 'update'])->name('admin.produccion.operations.update');
        Route::get('admin.produccion.operations.list', [ProdOperationController::class, 'list'])->name('admin.produccion.operations.list');
    });

    // CRUD Tarifas (Producto+Operación)
    Route::controller(ProdRateController::class)->group(function () {
        Route::get('admin.produccion.rates.index', [ProdRateController::class, 'index'])->name('admin.produccion.rates.index');
        Route::get('admin.produccion.rates.list', [ProdRateController::class, 'list'])->name('admin.produccion.rates.list');
        Route::post('admin.produccion.rates.store', [ProdRateController::class, 'store'])->name('admin.produccion.rates.store');
        Route::get('admin.produccion.rates.edit/{id}', [ProdRateController::class, 'edit'])->name('admin.produccion.rates.edit');
        Route::post('admin.produccion.rates.update/{id}', [ProdRateController::class, 'update'])->name('admin.produccion.rates.update');
    });

    // Routing (Operaciones por Orden) -> tab en Órdenes
    Route::controller(ProdOrderOperationController::class)->group(function () {
        Route::get('admin.produccion.orders.operations.index', [ProdOrderOperationController::class, 'index'])->name('admin.produccion.orders.operations.index');
        Route::get('admin.produccion.orders.operations/{orderId}', [ProdOrderOperationController::class, 'show'])->name('admin.produccion.orders.operations.show');
        Route::post('admin.produccion.orders.operations.store/{orderId}', [ProdOrderOperationController::class, 'store'])->name('admin.produccion.orders.operations.store');
        Route::get('admin.produccion.orders.operations.edit/{id}', [ProdOrderOperationController::class, 'edit'])->name('admin.produccion.orders.operations.edit');
        Route::post('admin.produccion.orders.operations.update/{id}', [ProdOrderOperationController::class, 'update'])->name('admin.produccion.orders.operations.update');
        Route::get('admin.produccion.orders.operations.list', [ProdOrderOperationController::class, 'listByOrder'])->name('admin.produccion.orders.operations.list');
    });

    // Sucursales
    Route::controller(BranchController::class)->group(function () {
        Route::get('admin.organization.branches', [BranchController::class,'index'])->name('admin.branches.index');
        Route::post('admin.organization.branches.store', [BranchController::class,'store'])->name('admin.branches.store');
        Route::get('admin.organization.branches.edit/{id}', [BranchController::class,'edit'])->name('admin.branches.edit');
        Route::post('admin.organization.branches.update/{id}', [BranchController::class,'update'])->name('admin.branches.update');
        Route::get('admin.organization.branches.list', [BranchController::class,'list'])->name('admin.branches.list');
    });

    // Centros de costo
    Route::controller(CostCenterController::class)->group(function () {
        Route::get('admin.organization.cost-centers', [CostCenterController::class,'index'])->name('admin.costCenters.index');
        Route::post('admin.organization.cost-centers.store', [CostCenterController::class,'store'])->name('admin.costCenters.store');
        Route::get('admin.organization.cost-centers.edit/{id}', [CostCenterController::class,'edit'])->name('admin.costCenters.edit');
        Route::post('admin.organization.cost-centers.update/{id}', [CostCenterController::class,'update'])->name('admin.costCenters.update');
        Route::get('admin.organization.cost-centers.list', [CostCenterController::class,'list'])->name('admin.costCenters.list');
    });


});
