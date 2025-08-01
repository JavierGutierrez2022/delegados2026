<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\MunicipalityController;

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
Route::resource('/admin/delegados',App\Http\Controllers\MiembroController::class);



Route::get('/admin/municipios/por-provincia/{id}', [\App\Http\Controllers\MunicipalityController::class, 'getByProvince']);
Route::get('/recintos/por-municipio/{municipality}', [\App\Http\Controllers\ElectoralPrecinctController::class, 'porMunicipio']);
Route::get('/mesas/por-recinto/{precinct}', [\App\Http\Controllers\TableController::class, 'porRecinto']);

Route::get('admin/delegados/reporte-agrupacion', [\App\Http\Controllers\MiembroController::class, 'reporteAgrupacion'])->name('delegados.reporte-agrupacion');







