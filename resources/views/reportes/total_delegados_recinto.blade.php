@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-4">Reporte de Delegados por Recinto</h1>

    <div class="row mb-3">
        
        <div class="col-md-4">
            <label for="provincia_id" class="form-label">Filtrar por Provincia</label>
            <select id="provincia_id" class="form-control">
                <option value="">Todas</option>
                @foreach($provincias as $provincia)
                    <option value="{{ $provincia->name }}">{{ $provincia->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="municipio_id" class="form-label">Filtrar por Municipio</label>
            <select id="municipio_id" class="form-control">
                <option value="">Todos</option>
                @foreach($municipios as $municipio)
                    <option value="{{ $municipio->name }}">{{ $municipio->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="recinto_search" class="form-label">Buscar Recinto</label>
            <input type="text" id="recinto_search" class="form-control" placeholder="Nombre del recinto...">
        </div>
    </div>

    <div class="mb-3 d-flex gap-2">
        <button id="btnExportExcel" class="btn btn-success">📥 Exportar Excel</button>
        <button id="btnExportCSV" class="btn btn-info">📄 Exportar CSV</button>
        <button id="btnExportPDF" class="btn btn-danger">📕 Exportar PDF</button>
        <button onclick="window.print()" class="btn btn-secondary">🖨️ Imprimir</button>
    </div>


    <table id="delegadosTable" class="table table-bordered">
        <thead>
            <tr>
                <th>Provincia</th>
                <th>Municipio</th>
                <th>Recinto</th>
                <th>Total Mesas</th>
                <th>Delegados Mesa</th>
                <th>Jefes Recinto</th>
                
                <th>Mesas con Miembros</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $fila)
                <tr data-municipio="{{ $fila->municipio }}">
                    <td>{{ $fila->provincia }}</td>
                    <td>{{ $fila->municipio }}</td>
                    <td>{{ $fila->recinto }}</td>
                    <td>{{ $fila->total_mesas }}</td>
                    <td>{{ $fila->cantidad_delegados_mesa }}</td>
                    <td>{{ $fila->cantidad_jefes_recinto }}</td>
                   
                    <td>{{ $fila->mesas_con_miembros }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport@5.2.0/dist/js/tableexport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>
<script>
function exportarTabla(formato) {
    const table = document.getElementById('delegadosTable');
    const exportInstance = new TableExport(table, {
        formats: [formato],
        filename: 'delegados_recinto',
        exportButtons: false
    });
    const exportData = exportInstance.getExportData();
    const exportContent = exportData.delegadosTable[formato];
    exportInstance.export2file(
        exportContent.data,
        exportContent.mimeType,
        exportContent.filename,
        exportContent.fileExtension
    );
}

document.getElementById('btnExportExcel').addEventListener('click', function () {
    exportarTabla('xlsx');
});

document.getElementById('btnExportCSV').addEventListener('click', function () {
    exportarTabla('csv');
});

document.getElementById('btnExportPDF').addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const table = document.getElementById('delegadosTable');
    
    // Extraer encabezados
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());

    // Extraer filas
    const data = Array.from(table.querySelectorAll('tbody tr')).map(row => {
        return Array.from(row.children).map(td => td.textContent.trim());
    });

    // Generar tabla con autoTable
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 20,
        styles: {
            fontSize: 8,
            cellPadding: 3,
        },
        headStyles: {
            fillColor: [22, 160, 133],
            textColor: 255,
            halign: 'center',
        },
        alternateRowStyles: {
            fillColor: [240, 240, 240],
        },
        margin: { top: 20 },
    });

    doc.save('delegados_recinto.pdf');
});



function aplicarFiltros() {
        const municipio = document.getElementById('municipio_id').value.toLowerCase();
        const provincia = document.getElementById('provincia_id').value.toLowerCase();
        const recinto = document.getElementById('recinto_search').value.toLowerCase();

        const rows = document.querySelectorAll('#delegadosTable tbody tr');
        rows.forEach(row => {
            const municipioText = row.children[1].textContent.toLowerCase();
            const provinciaText = row.children[0].textContent.toLowerCase();
            const recintoText = row.children[2].textContent.toLowerCase();

            const matchMunicipio = !municipio || municipioText.includes(municipio);
            const matchProvincia = !provincia || provinciaText.includes(provincia);
            const matchRecinto = !recinto || recintoText.includes(recinto);

            row.style.display = (matchMunicipio && matchProvincia && matchRecinto) ? '' : 'none';
        });
    }

    document.getElementById('municipio_id').addEventListener('change', aplicarFiltros);
    document.getElementById('provincia_id').addEventListener('change', aplicarFiltros);
    document.getElementById('recinto_search').addEventListener('input', aplicarFiltros);



</script>
@endpush
