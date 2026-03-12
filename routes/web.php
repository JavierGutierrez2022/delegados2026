<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentCrudController;
use App\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Route::get('/', function () {
  //  return view('welcome');
//});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Route::get('/', function () { return view('admin'); });

Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index')->middleware('auth');
/* Route::get('/', [App\Http\Controllers\AdminController::class, 'index2']); */
Route::get('/admin/usuarios', [App\Http\Controllers\UsuarioController::class, 'index'])->name('usuarios.index')->middleware('auth');
Route::get('/admin/usuarios/create', [App\Http\Controllers\UsuarioController::class, 'create'])->name('usuarios.create')->middleware('auth');
Route::post('/admin/usuarios', [App\Http\Controllers\UsuarioController::class, 'store'])->name('usuarios.store')->middleware('auth');
Route::get('/admin/usuarios/{id}', [App\Http\Controllers\UsuarioController::class, 'show'])->name('usuarios.show')->middleware('auth');
Route::get('/admin/usuarios/{id}/edit', [App\Http\Controllers\UsuarioController::class, 'edit'])->name('usuarios.edit')->middleware('auth');
Route::put('/admin/usuarios/{id}', [App\Http\Controllers\UsuarioController::class, 'update'])->name('usuarios.update')->middleware('auth');
Route::delete('/admin/usuarios/{id}', [App\Http\Controllers\UsuarioController::class, 'destroy'])->name('usuarios.destroy')->middleware('auth');

Route::get('/registro', [App\Http\Controllers\UsuarioController::class, 'registro'])->name('admin.index');
Route::post('/registro', [App\Http\Controllers\UsuarioController::class, 'registro_create'])->name('registro');
Route::get('/admin/mi_unidad',[App\Http\Controllers\CarpetaController::class, 'index'])->name('mi_unidad.index')->middleware('auth');
Route::post('/admin/mi_unidad',[App\Http\Controllers\CarpetaController::class, 'store'])->name('mi_unidad.store')->middleware('auth');
Route::get('/admin/mi_unidad/carpeta/{id}',[App\Http\Controllers\CarpetaController::class, 'show'])->name('mi_unidad.carpeta')->middleware('auth');
Route::post('/admin/mi_unidad/carpeta',[App\Http\Controllers\CarpetaController::class, 'crear_subcarpeta'])->name('mi_unidad.carpeta.crear_subcarpeta')->middleware('auth');

Route::get('/admin/delegados/index', [App\Http\Controllers\MiembroController::class, 'index'])->name('admin.delegados.index')->middleware('auth');
Route::get('/admin/delegados/create', [App\Http\Controllers\MiembroController::class, 'create'])->name('delegados.create')->middleware('auth');
Route::post('/admin/delegados',[App\Http\Controllers\MiembroController::class, 'store'])->name('delegados.store')->middleware('auth');
Route::get('/admin/delegados/importar', [App\Http\Controllers\MiembroController::class, 'importForm'])->name('delegados.import.form')->middleware(['auth','can:menu.importar_por_excel']);
Route::post('/admin/delegados/importar/preview', [App\Http\Controllers\MiembroController::class, 'importPreview'])->name('delegados.import.preview')->middleware(['auth','can:menu.importar_por_excel']);
Route::post('/admin/delegados/importar', [App\Http\Controllers\MiembroController::class, 'importStore'])->name('delegados.import.store')->middleware(['auth','can:menu.importar_por_excel']);
Route::get('/admin/delegados/plantilla-excel', [App\Http\Controllers\MiembroController::class, 'downloadTemplate'])->name('delegados.import.template')->middleware(['auth','can:menu.importar_por_excel']);
Route::resource('/admin/delegados',App\Http\Controllers\MiembroController::class);
Route::delete('delegados/{id}', [MiembroController::class, 'destroy'])->name('admin.delegados.destroy');



Route::get('/admin/municipios/por-provincia/{id}', [\App\Http\Controllers\MunicipalityController::class, 'getByProvince']);
Route::get('/admin/distritos/por-municipio/{id}', [\App\Http\Controllers\MunicipalityController::class, 'getDistrictsByMunicipality']);
Route::get('/recintos/por-municipio/{municipality}', [\App\Http\Controllers\ElectoralPrecinctController::class, 'porMunicipio']);
Route::get('/mesas/por-recinto/{precinct}', [\App\Http\Controllers\TableController::class, 'porRecinto']);
Route::get('admin/recintos/reporte', [\App\Http\Controllers\ElectoralPrecinctController::class, 'reporte'])
    ->middleware(['auth','can:menu.reportes'])
    ->name('recintos.reporte');
Route::middleware(['auth','can:menu.actualizar_recintos_por_excel'])->prefix('admin/recintos')->name('recintos.excel.')->group(function () {
    Route::get('actualizar-excel', [\App\Http\Controllers\RecintoMesaExcelController::class, 'form'])->name('form');
    Route::post('actualizar-excel/preview', [\App\Http\Controllers\RecintoMesaExcelController::class, 'preview'])->name('preview');
    Route::post('actualizar-excel', [\App\Http\Controllers\RecintoMesaExcelController::class, 'store'])->name('store');
    Route::get('actualizar-excel/plantilla', [\App\Http\Controllers\RecintoMesaExcelController::class, 'template'])->name('template');
});
Route::middleware(['auth','can:menu.actualizar_distritos_por_excel'])->prefix('admin/distritos')->name('distritos.excel.')->group(function () {
    Route::get('actualizar-excel', [\App\Http\Controllers\DistrictExcelController::class, 'form'])->name('form');
    Route::post('actualizar-excel/preview', [\App\Http\Controllers\DistrictExcelController::class, 'preview'])->name('preview');
    Route::post('actualizar-excel', [\App\Http\Controllers\DistrictExcelController::class, 'store'])->name('store');
    Route::get('actualizar-excel/plantilla', [\App\Http\Controllers\DistrictExcelController::class, 'template'])->name('template');
});
Route::get('/reportes/mesas-por-municipio', [\App\Http\Controllers\ReportController::class, 'mesasPorMunicipioYRecinto'])->middleware(['auth','can:menu.reportes'])->name('reportes.mesas_por_municipio');
Route::get('/reportes/reporte-agrupacion', [\App\Http\Controllers\MiembroController::class, 'reporteAgrupacion'])->middleware(['auth','can:menu.reportes'])->name('reportes.reporteAgrupacion');
Route::get('/reportes/detalle-mesas', [App\Http\Controllers\ReportController::class, 'reporteDetalleMesas'])->middleware(['auth','can:menu.reportes'])->name('reportes.detalle_mesas');

Route::get('/reportes/delegados-jefes', [App\Http\Controllers\ReportController::class, 'delegadosJefesRecinto'])->middleware(['auth','can:menu.reportes'])->name('reportes.delegados_jefes');
Route::get('reportes/mesas-por-provincia', [App\Http\Controllers\ReportController::class, 'mesasPorProvincia'])->middleware(['auth','can:menu.reportes'])->name('reportes.mesas_por_provincia');


Route::get('/reportes/total-delegados-recinto', [App\Http\Controllers\ReportController::class, 'vistaTotalDelegadosRecinto'])
    ->middleware(['auth','can:menu.reportes'])
    ->name('reportes.total_delegados_recinto');

Route::middleware(['auth','can:menu.roles'])->prefix('admin')->group(function () {
    Route::resource('roles', RoleController::class);
});

Route::middleware(['auth','can:menu.permisos'])->prefix('admin')->group(function () {
    Route::resource('permissions', PermissionController::class);
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::resource('usuarios', UsuarioController::class);
});

Route::middleware(['auth','can:menu.usuarios'])->prefix('admin')->group(function () {
    // ...
    Route::get('usuarios/{user}/roles', [UsuarioController::class,'editRoles'])->name('usuarios.roles.edit');
    Route::put('usuarios/{user}/roles', [UsuarioController::class,'updateRoles'])->name('usuarios.roles.update');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('postulaciones',        [PostulacionController::class,'index'])->name('postulaciones.index');
    Route::get('postulaciones/data',   [PostulacionController::class,'data'])->name('postulaciones.data');
    Route::get('postulaciones/excel',  [PostulacionController::class,'exportExcel'])->name('postulaciones.export.excel');
    Route::post('postulaciones/purge', [PostulacionController::class,'purge'])
        ->middleware('can:menu.usuarios')
        ->name('postulaciones.purge');

    // acciones
    Route::get('postulaciones/{miembro}',         [PostulacionController::class,'show'])->name('postulaciones.show');
    Route::get('postulaciones/{miembro}/edit',    [PostulacionController::class,'edit'])->name('postulaciones.edit');
    Route::put('postulaciones/{miembro}',         [PostulacionController::class,'update'])->name('postulaciones.update');
    Route::delete('postulaciones/{miembro}',      [PostulacionController::class,'destroy'])->name('postulaciones.destroy');
});
    
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Página única de asignación (conmutas RECINTO/MESA en el select)
    Route::get('asignaciones', [AssignmentController::class,'create'])->name('asignaciones.create');
    Route::post('asignaciones', [AssignmentController::class,'store'])->name('asignaciones.store');

    // JSON helpers
    Route::get('asignaciones/postulados', [AssignmentController::class,'postulados'])->name('asignaciones.postulados');
    Route::get('asignaciones/actuales', [AssignmentController::class,'actuales'])->name('asignaciones.actuales');

    // eliminar una asignación puntual (botón quitar)
    Route::delete('asignaciones/{assignment}', [AssignmentController::class,'destroy'])->name('asignaciones.destroy');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // LISTADO + CRUD de asignados
    Route::get('asignados',                [AssignmentCrudController::class, 'index'])->name('asignados.index');
    Route::get('asignados/data',           [AssignmentCrudController::class, 'data'])->name('asignados.data');
    Route::get('asignados/{assignment}',   [AssignmentCrudController::class, 'show'])->name('asignados.show');
    Route::get('asignados/{assignment}/edit', [AssignmentCrudController::class, 'edit'])->name('asignados.edit');
    Route::put('asignados/{assignment}',   [AssignmentCrudController::class, 'update'])->name('asignados.update');
    Route::delete('asignados/{assignment}',[AssignmentCrudController::class, 'destroy'])->name('asignados.destroy');
});

// === REPORTE DE COBERTURA ===
Route::prefix('reportes')->middleware(['auth','can:menu.cobertura_mesas'])->group(function () {
    Route::get('/cobertura',        [\App\Http\Controllers\ReporteCoberturaController::class,'index'])->name('cobertura.index');
    Route::get('/cobertura/data',   [\App\Http\Controllers\ReporteCoberturaController::class,'data'])->name('cobertura.data');       // DataTables
    Route::get('/cobertura/resumen',[\App\Http\Controllers\ReporteCoberturaController::class,'resumen'])->name('cobertura.resumen'); // KPIs
    Route::get('/cobertura/matriz', [\App\Http\Controllers\ReporteCoberturaController::class,'matriz'])->name('cobertura.matriz');
    Route::get('/cobertura/matriz/excel', [\App\Http\Controllers\ReporteCoberturaController::class,'matrizExcel'])->name('cobertura.matriz.excel');
});
Route::middleware(['auth','can:menu.auditorias'])->prefix('admin/actividad')->name('actividad.')->group(function () {
    Route::get('/',          [ActivityLogController::class,'index'])->name('index');
    Route::get('/data',      [ActivityLogController::class,'data'])->name('data');
    Route::get('/{actividad}',[ActivityLogController::class,'show'])->name('show');
    Route::delete('/{actividad}',[ActivityLogController::class,'destroy'])->name('destroy');
    Route::post('/purge',    [ActivityLogController::class,'purge'])->name('purge');
});

Route::middleware(['auth','can:menu.datos_prueba'])->prefix('admin/configuracion')->name('staging.')->group(function () {
    Route::get('/datos-prueba', [App\Http\Controllers\StagingController::class, 'index'])->name('index');
    Route::post('/datos-prueba/seed', [App\Http\Controllers\StagingController::class, 'seed'])->name('seed');
    Route::post('/datos-prueba/seed-postulantes', [App\Http\Controllers\StagingController::class, 'seedPostulantes'])->name('seed.postulantes');
    Route::post('/datos-prueba/seed-jefes', [App\Http\Controllers\StagingController::class, 'seedJefes'])->name('seed.jefes');
    Route::post('/datos-prueba/seed-delegados', [App\Http\Controllers\StagingController::class, 'seedDelegados'])->name('seed.delegados');
    Route::post('/datos-prueba/clear', [App\Http\Controllers\StagingController::class, 'clear'])->name('clear');
});
