@extends('layouts.admin')

@section('content')
<div class="container-fluid">

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="fas fa-users"></i> Asignación De Postulados</h5>
    </div>
    <div class="card-body">

      {{-- Configuración de ubicación --}}
      <h6 class="mb-3"><i class="fas fa-map-marker-alt"></i> Configuración de Ubicación</h6>

      <form id="form-asigna" method="POST" action="{{ route('asignaciones.store') }}">
        @csrf

        <div class="row g-3 align-items-end mb-4">
          <div class="col-md-3">
            <label class="form-label">Tipo de Rol</label>
            <select name="scope" id="scope" class="form-control">
              <option value="">-- Seleccionar Tipo de Rol --</option>
              <option value="RECINTO">RECINTO</option>
              <option value="MESA">MESA</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Departamento</label>
            <input type="text" class="form-control" value="Tarija" disabled>
          </div>
          <div class="col-md-3">
            <label class="form-label">Provincia</label>
            <select name="province_id" id="province_id" class="form-control">
              <option value="">-- Seleccionar Provincia --</option>
              @foreach($provincias as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Municipio</label>
            <select name="municipality_id" id="municipality_id" class="form-control">
              <option value="">-- Seleccionar Municipio --</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Recinto</label>
            <select name="electoral_precinct_id" id="electoral_precinct_id" class="form-control">
              <option value="">-- Seleccionar Recinto --</option>
            </select>
          </div>
          <div class="col-md-3 d-none" id="wrap-mesa">
            <label class="form-label">Mesa</label>
            <select name="table_id" id="table_id" class="form-control">
              <option value="">-- Seleccionar Mesa --</option>
            </select>
          </div>
        </div>

        {{-- CONFIGURACIÓN DE RECINTO / MESA --}}
        <div class="row g-3">
          <div class="col-xl-6">
            <div class="card">
              <div class="card-header bg-success text-white">
                <i class="fas fa-users"></i> Postulados Disponibles
              </div>
              <div class="card-body">
                <div class="input-group mb-2">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                  <input type="text" id="busca" class="form-control" placeholder="Buscar postulado...">
                </div>
                <div id="list-postulados" class="list-group" style="max-height: 320px; overflow:auto;">
                  <div class="text-muted small">Seleccione filtros para listar postulados…</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-6">
            <div class="card">
              <div id="roles-header" class="card-header text-white" style="background:#ff7f4d">
                <i class="fas fa-user-shield"></i> Roles
              </div>
              <div class="card-body">

                {{-- Slots dinámicos para roles --}}
                <div id="slots-roles" class="vstack gap-3">
                  {{-- Se llenan por JS según scope --}}
                </div>

                <div id="jefe-mesas-panel" class="border rounded p-3 mt-3 d-none">
                  <div class="small text-muted mb-2" id="jefe-mesas-hint">
                    Seleccione un recinto para elegir las mesas que quedarán a cargo del jefe.
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="assign_jefe_to_tables" name="assign_jefe_to_tables" value="1">
                    <label class="form-check-label" for="assign_jefe_to_tables">
                      Cargar al Jefe de Recinto también como Delegado de Mesa Propietario
                    </label>
                  </div>

                  <div id="jefe-mesas-options" class="d-none">
                    <div class="row g-2 align-items-end">
                      <div class="col-md-4">
                        <label class="form-label">Cobertura en mesas</label>
                        <select id="jefe_table_mode" name="jefe_table_mode" class="form-control">
                          <option value="">-- Seleccionar opción --</option>
                          <option value="ALL">Todas las mesas del recinto</option>
                          <option value="SELECTED">Mesas específicas</option>
                        </select>
                      </div>
                      <div class="col-md-8 d-none" id="wrap-jefe-table-ids">
                        <label class="form-label">Mesas a cargo</label>
                        <select id="jefe_table_ids" name="jefe_table_ids[]" class="form-control" multiple></select>
                        <small class="text-muted">Puede seleccionar una o varias mesas del recinto.</small>
                      </div>
                    </div>
                  </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Asignación
                  </button>
                                                 
                </div>
              </div>
            </div>
          </div>
        </div>

      </form>

      <div class="alert alert-warning mt-4">
        <i class="fas fa-exclamation-triangle"></i>
        RECUERDE QUE PARA COMPLETAR LA ASIGNACIÓN DEBE PRESIONAR EL BOTÓN   "Guardar Asignación"
      </div>

    </div>
  </div>
</div>
@endsection

<style>
  /* resalta el slot cuando arrastras encima */
  .slot-rol.drop-hover{ outline: 2px dashed #3b82f6; background:#eef6ff; }
  /* cursor de arrastre cuando el slot tiene un asignado */
  .slot-rol[draggable="true"]{ cursor: grab; }
</style>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
@push('scripts')
<script>
$(function () {
  let recintoMesasCache = [];

  // Select2 para Recinto (con buscador)
  $('#electoral_precinct_id').select2({
    placeholder: '-- Seleccionar Recinto --',
    allowClear: true,
    width: '100%'
  });
  $('#jefe_table_ids').select2({
    placeholder: '-- Seleccionar Mesas --',
    allowClear: true,
    width: '100%'
  });

  // Combos dependientes
  $('#province_id').on('change', function(){
    const id = $(this).val();
    $('#municipality_id').html('<option value="">-- Seleccionar Municipio --</option>');
    $('#electoral_precinct_id').html('<option value="">-- Seleccionar Recinto --</option>');
    $('#electoral_precinct_id').trigger('change.select2');
    $('#table_id').html('<option value="">-- Seleccionar Mesa --</option>');
    toggleJefeMesasPanel();
    if(!id) return;
    $.get("{{ url('/admin/municipios/por-provincia') }}/"+id, function(res){
      let opts = '<option value="">-- Seleccionar Municipio --</option>';
      res.forEach(r => opts += `<option value="${r.id}">${r.name}</option>`);
      $('#municipality_id').html(opts);
      cargarPostulados();
    });
  });

  $('#municipality_id').on('change', function(){
    const id = $(this).val();
    $('#electoral_precinct_id').html('<option value="">-- Seleccionar Recinto --</option>');
    $('#electoral_precinct_id').trigger('change.select2');
    $('#table_id').html('<option value="">-- Seleccionar Mesa --</option>');
    toggleJefeMesasPanel();
    if(!id) return;
    $.get("{{ url('/recintos/por-municipio') }}/"+id, function(res){
      let opts = '<option value="">-- Seleccionar Recinto --</option>';
      res.forEach(r => opts += `<option value="${r.id}">${r.name}</option>`);
      $('#electoral_precinct_id').html(opts);
      $('#electoral_precinct_id').trigger('change.select2');
      cargarPostulados();
    });
  });

  $('#electoral_precinct_id').on('change', function(){
    const precinctId = $(this).val();
    $('#table_id').html('<option value="">-- Seleccionar Mesa --</option>');
    resetJefeMesasOptions();
    toggleJefeMesasPanel();
    if(precinctId){
      $.get("{{ url('/mesas/por-recinto') }}/"+precinctId, function(res){
        recintoMesasCache = res || [];
        let opts = '<option value="">-- Seleccionar Mesa --</option>';
        recintoMesasCache.forEach(m => opts += `<option value="${m.id}">Mesa ${m.table_number}</option>`);
        if($('#scope').val()==='MESA'){
          $('#table_id').html(opts);
        }
        refreshJefeMesasSelect();
      });
    } else {
      recintoMesasCache = [];
    }
    cargarPostulados();
    cargarAsignados();
  });

  $('#table_id').on('change', function(){
    cargarPostulados();
    cargarAsignados();
  });

  // Cambiar scope cambia roles visibles
  $('#scope').on('change', function(){
    const scope = $(this).val();
    $('#wrap-mesa').toggleClass('d-none', scope!=='MESA');
    dibujarSlots(scope);
    toggleJefeMesasPanel();
    cargarAsignados();
  });

  $('#assign_jefe_to_tables').on('change', function(){
    if(!$('#electoral_precinct_id').val()){
      $(this).prop('checked', false);
      Swal.fire({icon:'warning', title:'Seleccione primero el recinto'});
      return;
    }
    $('#jefe-mesas-options').toggleClass('d-none', !this.checked);
    if(!this.checked){
      $('#jefe_table_mode').val('');
      $('#jefe_table_ids').val(null).trigger('change');
      $('#wrap-jefe-table-ids').addClass('d-none');
    }
  });

  $('#jefe_table_mode').on('change', function(){
    const showSpecific = $(this).val() === 'SELECTED';
    $('#wrap-jefe-table-ids').toggleClass('d-none', !showSpecific);
    if (!showSpecific) {
      $('#jefe_table_ids').val(null).trigger('change');
    }
  });

  // Buscar
  $('#busca').on('keyup', _.debounce(function(){
    cargarPostulados();
  }, 300));

  // Carga inicial de slots
  dibujarSlots($('#scope').val());

  // Click en lista de postulados → asigna al primer slot vacío
  $(document).on('click', '.itm-postulado', function(){
    const mid   = $(this).data('id');
    const name  = $(this).data('name');
    // primer slot vacío
    let slot = $('#slots-roles .slot-rol').filter(function(){ return !$(this).data('miembro-id'); }).first();
    if(!slot.length){ Swal.fire({icon:'info', title:'Todos los roles están ocupados'}); return; }
    setSlot(slot, mid, name);
  });

  // quitar asignado
  $(document).on('click','.btn-clear-slot', function(){
    clearSlot($(this).closest('.slot-rol'));
  });

  // Enviar form → construye payload roles[ROL]=miembro_id
  $('#form-asigna').on('submit', function(e){
    // valida básicos
    const scope = $('#scope').val();
    const prec  = $('#electoral_precinct_id').val();
    if(!scope || !prec){
      e.preventDefault();
      Swal.fire({icon:'warning', title:'Seleccione Tipo de Rol y Recinto'});
      return false;
    }
    if(scope==='MESA' && !$('#table_id').val()){
      e.preventDefault();
      Swal.fire({icon:'warning', title:'Seleccione la Mesa'});
      return false;
    }
    if(scope==='RECINTO' && $('#assign_jefe_to_tables').is(':checked')){
      if(!$('#slots-roles .slot-rol[data-role="JEFE_DE_RECINTO"]').data('miembro-id')){
        e.preventDefault();
        Swal.fire({icon:'warning', title:'Debe asignar un Jefe de Recinto antes de cargarlo en mesas'});
        return false;
      }
      if(!$('#jefe_table_mode').val()){
        e.preventDefault();
        Swal.fire({icon:'warning', title:'Seleccione si el jefe cubrirá todas las mesas o mesas específicas'});
        return false;
      }
      if($('#jefe_table_mode').val()==='SELECTED' && (!$('#jefe_table_ids').val() || $('#jefe_table_ids').val().length===0)){
        e.preventDefault();
        Swal.fire({icon:'warning', title:'Seleccione al menos una mesa para el jefe'});
        return false;
      }
    }

    // limpia roles vacíos (para no enviar nulls)
    $('#slots-roles .slot-rol').each(function(){
      if(!$(this).data('miembro-id')){
        $(this).find('input[type=hidden]').remove();
      }
    });
  });

  function dibujarSlots(scope){
    const $wrap = $('#slots-roles').empty();
    if(scope==='RECINTO'){
      $('#roles-header').css('background','#ff7f4d').text(' Roles de Recinto').prepend('<i class="fas fa-user-shield"></i>');
      $wrap.append(slotHTML('JEFE_DE_RECINTO','Jefe de Recinto'));
      $wrap.append(slotHTML('MONITOR_RADAR','Monitor / Radar'));
    } else if(scope==='MESA'){
      $('#roles-header').css('background','#26c6da').text(' Roles de Mesa').prepend('<i class="fas fa-user-shield"></i>');
      $wrap.append(slotHTML('DELEGADO_PROPIETARIO','Delegado de Mesa Propietario'));
      $wrap.append(slotHTML('DELEGADO_SUPLENTE','Delegado de Mesa Suplente'));
    } else {
      $('#roles-header').css('background','#9e9e9e').text(' Roles');
    }
    toggleJefeMesasPanel();
  }

  function slotHTML(code, label){
    return `
      <div class="slot-rol border rounded p-2" data-role="${code}">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="small text-muted">${label}</div>
            <div class="fw-bold nombre-asignado">—</div>
          </div>
          <button type="button" class="btn btn-outline-secondary btn-sm btn-clear-slot" title="Quitar" disabled>
            <i class="fas fa-times"></i>
          </button>
        </div>
        <input type="hidden" name="roles[${code}]" value="">
      </div>
    `;
  }

    function setSlot($slot, miembroId, nombre){
    $slot.data('miembro-id', miembroId);
    $slot.find('.nombre-asignado').text(nombre);
    $slot.find('input[type=hidden]').val(miembroId);
    $slot.find('.btn-clear-slot').prop('disabled', false);
    $slot.attr('draggable', true); // <-- permite arrastrar el slot con asignado
    }

    function clearSlot($slot){
    $slot.data('miembro-id', '');
    $slot.find('.nombre-asignado').text('—');
    $slot.find('input[type=hidden]').val('');
    $slot.find('.btn-clear-slot').prop('disabled', true);
    $slot.removeAttr('draggable'); // <-- desactiva arrastre si está vacío
    }

        function cargarPostulados(){
            const params = {
                scope: $('#scope').val(),
                province_id: $('#province_id').val(),
                municipality_id: $('#municipality_id').val(),
                electoral_precinct_id: $('#electoral_precinct_id').val(),
                table_id: $('#table_id').val(),
                term: $('#busca').val()
            };
            if(!params.electoral_precinct_id){
                $('#list-postulados').html('<div class="text-muted small">Seleccione un recinto…</div>');
                return;
            }

            $.get("{{ route('asignaciones.postulados') }}", params, function(res){
                if(!res.length){ $('#list-postulados').html('<div class="text-muted small">Sin resultados…</div>'); return; }
                let html = '';
                res.forEach(x => {
                const nombre = (x.nombres||'')+' '+(x.app||'')+' '+(x.apm||'');
                html += `
                    <a href="javascript:void(0)"
                    class="list-group-item list-group-item-action itm-postulado"
                    draggable="true"
                    data-id="${x.id}"
                    data-name="${nombre.trim()}">
                    <div class="d-flex justify-content-between">
                        <div><strong>${nombre}</strong><div class="small text-muted">CI: ${x.ci||'—'}</div></div>
                        <div class="text-muted small">${x.celular||''}</div>
                    </div>
                    </a>`;
                });
                $('#list-postulados').html(html);
            });
        }

  function cargarAsignados(){
    const scope = $('#scope').val();
    const precinct = $('#electoral_precinct_id').val();
    const tableId = $('#table_id').val();
    if(!scope || !precinct) return;

    $.get("{{ route('asignaciones.actuales') }}", {scope:scope, electoral_precinct_id:precinct, table_id:tableId}, function(res){
      // limpia slots
      $('#slots-roles .slot-rol').each(function(){ clearSlot($(this)); });
      // pinta
      res.forEach(item => {
        const $slot = $(`#slots-roles .slot-rol[data-role="${item.role}"]`);
        if($slot.length){
          setSlot($slot, item.miembro.id, item.miembro.nombre);
        }
      });
    });
  }

  function toggleJefeMesasPanel(){
    const show = $('#scope').val()==='RECINTO';
    $('#jefe-mesas-panel').toggleClass('d-none', !show);
    $('#assign_jefe_to_tables').prop('disabled', !$('#electoral_precinct_id').val());
    $('#jefe-mesas-hint').toggleClass('d-none', !!$('#electoral_precinct_id').val());
    if(!show){
      resetJefeMesasOptions();
    }
  }

  function resetJefeMesasOptions(){
    $('#assign_jefe_to_tables').prop('checked', false);
    $('#jefe-mesas-options').addClass('d-none');
    $('#jefe_table_mode').val('');
    $('#jefe_table_ids').empty().trigger('change');
    $('#wrap-jefe-table-ids').addClass('d-none');
  }

  function refreshJefeMesasSelect(){
    const options = recintoMesasCache.map(m => `<option value="${m.id}">Mesa ${m.table_number}</option>`);
    $('#jefe_table_ids').html(options.join('')).trigger('change');
  }
});

// Arrastrar desde la lista de postulados
$(document).on('dragstart', '.itm-postulado', function(e){
  const payload = { id: $(this).data('id'), name: $(this).data('name'), from: 'list' };
  e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(payload));
});

// Permitir soltar sobre slots
$('#slots-roles')
  .on('dragover', '.slot-rol', function(e){ e.preventDefault(); $(this).addClass('drop-hover'); })
  .on('dragleave', '.slot-rol', function(){ $(this).removeClass('drop-hover'); })
  .on('drop', '.slot-rol', function(e){
    e.preventDefault();
    $(this).removeClass('drop-hover');

    let data = {};
    try { data = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain') || '{}'); } catch(_){}

    if(!data.id) return;

    // si estaba ya en otro slot, quítalo del anterior (mover)
    const $prev = $('#slots-roles .slot-rol').filter(function(){ return $(this).data('miembro-id') == data.id; });
    if($prev.length){ clearSlot($prev); }

    // asigna al slot destino
    setSlot($(this), data.id, data.name);
  });

  // Arrastrar un asignado desde un slot
$('#slots-roles').on('dragstart', '.slot-rol', function(e){
  const mid = $(this).data('miembro-id');
  if(!mid){ e.preventDefault(); return; }
  const name = $(this).find('.nombre-asignado').text();
  const payload = { id: mid, name: name, from: 'slot' };
  $(this).addClass('drag-origin');
  e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(payload));
});
$('#slots-roles').on('dragend', '.slot-rol', function(){
  $(this).removeClass('drag-origin');
});

</script>
@endpush
