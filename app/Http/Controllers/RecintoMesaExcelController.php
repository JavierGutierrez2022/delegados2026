<?php

namespace App\Http\Controllers;

use App\Exports\RecintosMesasTemplateExport;
use App\Imports\RecintosMesasUpdateImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RecintoMesaExcelController extends Controller
{
    public function form()
    {
        return view('admin.recintos.excel_update');
    }

    public function template()
    {
        return Excel::download(new RecintosMesasTemplateExport(), 'plantilla_actualizacion_recintos_mesas.xlsx');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $import = new RecintosMesasUpdateImport(true);
        Excel::import($import, $request->file('archivo'));

        return redirect()
            ->route('recintos.excel.form')
            ->with('preview_summary', $import->summary())
            ->with('preview_errors', array_slice($import->errors, 0, 120))
            ->with('mensaje', 'Prevalidación completada. Revise el resultado antes de aplicar cambios.')
            ->with('icono', count($import->errors) > 0 ? 'warning' : 'success');
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $import = new RecintosMesasUpdateImport(false);
        Excel::import($import, $request->file('archivo'));

        return redirect()
            ->route('recintos.excel.form')
            ->with('import_summary', $import->summary())
            ->with('import_errors', array_slice($import->errors, 0, 120))
            ->with('mensaje', 'Actualización de recintos/mesas finalizada.')
            ->with('icono', count($import->errors) > 0 ? 'warning' : 'success');
    }
}

