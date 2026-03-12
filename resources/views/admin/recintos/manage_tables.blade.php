@extends('layouts.admin')

@section('content')
<div class="content">
    <h1>Modificar mesas de recintos</h1>
</div>

<style>
    #modalEditarMesas .modal-content {
        max-height: calc(100vh - 40px);
    }

    #modalEditarMesas .modal-body {
        max-height: calc(100vh - 240px);
        overflow-y: auto;
    }

    #modalEditarMesas .modal-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        z-index: 2;
        border-top: 1px solid #dee2e6;
    }

    #modalMesasEditor {
        max-height: 38vh;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 4px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-grid-3x3-gap"></i> Ajuste manual de mesas</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Esta pantalla no crea ni modifica tablas de la base de datos. Solo agrega, quita o renumera filas en <code>tables</code> para cada recinto y sincroniza el contador resumen del recinto.
                </div>

                <div class="alert alert-warning">
                    Si quitas una mesa usada en asignaciones o en <code>miembro_table</code>, el sistema bloqueara el cambio. La renumeracion de una mesa existente si esta permitida.
                </div>

                <form method="GET" action="{{ route('recintos.manage.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Provincia</label>
                            <select name="province_id" id="province_id" class="form-control">
                                <option value="">TODAS</option>
                                @foreach($provincias as $provincia)
                                    <option value="{{ $provincia->id }}" @selected(request('province_id') == $provincia->id)>{{ $provincia->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Municipio</label>
                            <select name="municipality_id" id="municipality_id" class="form-control">
                                <option value="">TODOS</option>
                                @foreach($municipios as $municipio)
                                    <option value="{{ $municipio->id }}" @selected(request('municipality_id') == $municipio->id)>{{ $municipio->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="">TODOS</option>
                                <option value="ACTIVO" @selected(request('estado') === 'ACTIVO')>ACTIVO</option>
                                <option value="INACTIVO" @selected(request('estado') === 'INACTIVO')>INACTIVO</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Recinto</label>
                            <select name="precinct_id" id="precinct_id" class="form-control">
                                <option value="">TODOS</option>
                                @foreach($recintosFiltro as $recintoFiltro)
                                    <option value="{{ $recintoFiltro->id }}" @selected(request('precinct_id') == $recintoFiltro->id)>{{ $recintoFiltro->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label>Buscar general</label>
                            <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Nombre, asiento o municipio">
                        </div>
                    </div>
                    <div class="mt-3 d-flex flex-wrap" style="gap: .5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route('recintos.manage.index') }}" class="btn btn-secondary">
                            <i class="bi bi-eraser"></i> Limpiar
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>Provincia</th>
                                <th>Municipio</th>
                                <th>Distrito</th>
                                <th>Asiento</th>
                                <th>Recinto</th>
                                <th>Estado</th>
                                <th>Mesas actuales</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recintos as $recinto)
                                <tr>
                                    <td>{{ $recinto->province_name }}</td>
                                    <td>{{ $recinto->municipality_name }}</td>
                                    <td>{{ $recinto->district_name ?? '-' }}</td>
                                    <td>{{ $recinto->electoral_seat ?: '-' }}</td>
                                    <td>{{ $recinto->name }}</td>
                                    <td>
                                        <span class="badge {{ strtoupper($recinto->state) === 'ACTIVO' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $recinto->state }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $recinto->current_tables }}</strong></td>
                                    <td style="min-width: 140px;">
                                        <button
                                            type="button"
                                            class="btn btn-warning btn-block js-open-edit-modal"
                                            data-toggle="modal"
                                            data-target="#modalEditarMesas"
                                            data-id="{{ $recinto->id }}"
                                            data-provincia="{{ $recinto->province_name }}"
                                            data-municipio="{{ $recinto->municipality_name }}"
                                            data-distrito="{{ $recinto->district_name ?? '-' }}"
                                            data-asiento="{{ $recinto->electoral_seat ?: '-' }}"
                                            data-recinto="{{ $recinto->name }}"
                                            data-actual="{{ $recinto->current_tables }}"
                                        >
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron recintos con esos filtros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $recintos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarMesas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="modalEditarMesasForm" method="POST" class="js-update-mesas-form">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Editar mesas de recinto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div><strong>Provincia:</strong> <span id="modalProvincia"></span></div>
                                <div><strong>Municipio:</strong> <span id="modalMunicipio"></span></div>
                                <div><strong>Distrito:</strong> <span id="modalDistrito"></span></div>
                                <div><strong>Asiento:</strong> <span id="modalAsiento"></span></div>
                                <div><strong>Recinto:</strong> <span id="modalRecinto"></span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="form-group mb-2">
                                    <label>Mesas actuales</label>
                                    <input type="text" id="modalMesasActuales" class="form-control" readonly>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Total a guardar</label>
                                    <input type="text" id="modalMesaTotal" class="form-control" readonly>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-block" id="btnAgregarMesa">
                                    <i class="bi bi-plus-circle"></i> Agregar mesa
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Detalle editable de mesas</strong>
                            <span class="badge badge-info" id="modalDetalleCount">0 mesas</span>
                        </div>
                        <div id="modalMesasLoader" class="text-muted">Selecciona un recinto para ver el detalle.</div>
                        <div id="modalMesasVacias" class="text-muted d-none">Este recinto no tiene mesas registradas.</div>
                        <div id="modalMesasEditor"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    let nextNewMesaNumber = 1;

    function updateMesaCounters() {
        const count = $('#modalMesasEditor .js-mesa-row').length;
        $('#modalMesaTotal').val(count);
        $('#modalDetalleCount').text(count + ' mesas');
        $('#modalMesasVacias').toggleClass('d-none', count > 0);
    }

    function buildMesaRow(id, number, isNew = false) {
        const safeId = id || '';
        const badge = isNew
            ? '<span class="badge badge-primary mr-2">Nueva</span>'
            : '<span class="badge badge-secondary mr-2">Existente</span>';

        return `
            <div class="row align-items-center mb-2 js-mesa-row">
                <div class="col-md-3 mb-2 mb-md-0">
                    ${badge}<strong>Mesa</strong>
                </div>
                <div class="col-md-5 mb-2 mb-md-0">
                    <input type="hidden" name="table_ids[]" value="${safeId}">
                    <input type="number" name="table_numbers[]" class="form-control js-mesa-number" min="1" value="${number}" required>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-danger btn-block js-remove-mesa">
                        <i class="bi bi-trash"></i> Quitar mesa
                    </button>
                </div>
            </div>
        `;
    }

    $('#precinct_id').select2({
        width: '100%',
        placeholder: 'TODOS',
        allowClear: true
    });

    $('#province_id').on('change', function () {
        const provinceId = $(this).val();
        const $municipality = $('#municipality_id');
        const $precinct = $('#precinct_id');

        $municipality.html('<option value="">TODOS</option>');
        $precinct.html('<option value="">TODOS</option>').val('').trigger('change');

        if (!provinceId) {
            return;
        }

        $.get("{{ url('/admin/municipios/por-provincia') }}/" + provinceId, function (items) {
            $.each(items, function (_, municipio) {
                $municipality.append('<option value="' + municipio.id + '">' + municipio.name + '</option>');
            });
        });
    });

    $('#municipality_id').on('change', function () {
        const municipalityId = $(this).val();
        const $precinct = $('#precinct_id');

        $precinct.html('<option value="">TODOS</option>').val('').trigger('change');

        if (!municipalityId) {
            return;
        }

        $.get("{{ url('/recintos/por-municipio') }}/" + municipalityId, function (items) {
            $.each(items, function (_, recinto) {
                $precinct.append('<option value="' + recinto.id + '">' + recinto.name + '</option>');
            });
            $precinct.trigger('change');
        });
    });

    $('#btnAgregarMesa').on('click', function () {
        $('#modalMesasEditor').append(buildMesaRow('', nextNewMesaNumber, true));
        nextNewMesaNumber++;
        updateMesaCounters();
    });

    $(document).on('click', '.js-remove-mesa', function () {
        $(this).closest('.js-mesa-row').remove();
        updateMesaCounters();
    });

    $('.js-update-mesas-form').on('submit', function () {
        return confirm('Se actualizara la configuracion de mesas del recinto. ¿Deseas continuar?');
    });

    $('.js-open-edit-modal').on('click', function () {
        const precinctId = $(this).data('id');
        const actualCount = Number($(this).data('actual')) || 0;

        $('#modalProvincia').text($(this).data('provincia'));
        $('#modalMunicipio').text($(this).data('municipio'));
        $('#modalDistrito').text($(this).data('distrito'));
        $('#modalAsiento').text($(this).data('asiento'));
        $('#modalRecinto').text($(this).data('recinto'));
        $('#modalMesasActuales').val(actualCount);
        $('#modalMesaTotal').val(actualCount);
        $('#modalEditarMesasForm').attr('action', "{{ url('admin/recintos') }}/" + precinctId + "/modificar-mesas");

        const $loader = $('#modalMesasLoader');
        const $empty = $('#modalMesasVacias');
        const $editor = $('#modalMesasEditor');

        $loader.removeClass('d-none').text('Cargando mesas...');
        $empty.addClass('d-none');
        $editor.empty();

        $.get("{{ url('/mesas/por-recinto') }}/" + precinctId, function (items) {
            const mesas = (items || []).slice().sort((a, b) => Number(a.table_number) - Number(b.table_number));

            $loader.addClass('d-none');

            if (!mesas.length) {
                nextNewMesaNumber = 1;
                updateMesaCounters();
                return;
            }

            mesas.forEach(function (mesa) {
                $editor.append(buildMesaRow(mesa.id, mesa.table_number, false));
            });

            nextNewMesaNumber = Math.max.apply(null, mesas.map(item => Number(item.table_number) || 0)) + 1;
            updateMesaCounters();
        }).fail(function () {
            $loader.removeClass('d-none').text('No se pudo cargar el detalle de mesas.');
            $('#modalDetalleCount').text('Sin datos');
        });
    });
});
</script>
@endsection
