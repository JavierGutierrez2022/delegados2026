<?php

namespace App\Http\Controllers;

use App\Exports\DistrictsByRecintoTemplateExport;
use App\Imports\DistrictsByRecintoImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DistrictExcelController extends Controller
{
    public function form()
    {
        return view('admin.distritos.excel_update');
    }

    public function template()
    {
        return Excel::download(new DistrictsByRecintoTemplateExport(), 'plantilla_distritos_tarija.xlsx');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $import = new DistrictsByRecintoImport(true);
        Excel::import($import, $request->file('archivo'));

        return redirect()
            ->route('distritos.excel.form')
            ->with('preview_summary', $import->summary())
            ->with('preview_errors', array_slice($import->errors, 0, 150))
            ->with('mensaje', 'Prevalidacion de distritos completada.')
            ->with('icono', count($import->errors) > 0 ? 'warning' : 'success');
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $import = new DistrictsByRecintoImport(false);
        Excel::import($import, $request->file('archivo'));

        return redirect()
            ->route('distritos.excel.form')
            ->with('import_summary', $import->summary())
            ->with('import_errors', array_slice($import->errors, 0, 150))
            ->with('mensaje', 'Actualizacion de distritos aplicada.')
            ->with('icono', count($import->errors) > 0 ? 'warning' : 'success');
    }
}

