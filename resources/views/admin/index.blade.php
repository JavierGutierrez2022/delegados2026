@extends('layouts.admin')

@section('content')

@php
$metrics = $metrics ?? [
  'mesas'   => ['total'=>0,'cubiertas'=>0,'pct'=>0],
  'recintos'=> ['total'=>0,'cubiertos'=>0,'pct'=>0],
];
@endphp

<style>
  /* ===== KPI Cards estilo "glass + gradient" ===== */
  .kpi-card{
    position:relative;
    border-radius:16px;
    overflow:hidden;
    color:#fff;
    box-shadow:0 10px 24px rgba(0,0,0,.18);
    transition:transform .2s ease, box-shadow .2s ease, filter .2s ease;
    backdrop-filter: blur(4px);
    min-height: 158px;
  }
  .kpi-card:hover{
    transform: translateY(-2px);
    box-shadow:0 14px 30px rgba(0,0,0,.24);
    filter: saturate(1.05);
  }
  .kpi-body{
    padding:22px 22px 58px 22px;
    display:flex; 
    gap: 12px;
    align-items: flex-start;
  }
  .kpi-count{
    font-weight:800;
    font-size:44px;
    line-height:1;
    letter-spacing:.5px;
  }
  .kpi-title{
    margin-top:8px;
    font-size:18px;
    font-weight:600;
    opacity:.95;
  }
  .kpi-icon{
    margin-left:auto;
    font-size:60px;
    opacity:.25;
  }
  .kpi-footer{
    position:absolute;
    left:0; right:0; bottom:0;
    padding:12px 18px;
    background:rgba(0,0,0,.18);
    display:flex; justify-content:space-between; align-items:center;
    font-weight:600;
    color:#fff;
    text-decoration:none;
    transition: background .2s ease, padding-right .2s ease;
  }
  .kpi-footer .bi, .kpi-footer .fas{ opacity:.85; }
  .kpi-footer:hover{ background:rgba(0,0,0,.26); padding-right:24px; }

  /* Degradados */
  .kpi-blue{  background:linear-gradient(135deg,#2fa1ff,#1c7ef9 65%); }
  .kpi-green{ background:linear-gradient(135deg,#28cc8b,#1aae6a 65%); }
  .kpi-slate{ background:linear-gradient(135deg,#afbb7a,#ecd50c 65%); }
  .kpi-indigo{background:linear-gradient(135deg,#f3d77a,#d98b42 65%); }
  .kpi-morado{background:linear-gradient(135deg,#766497,#8342d9 65%); }
  .kpi-rosado{background:linear-gradient(135deg,#97647c,#d9429f 65%); }

  /* Separación consistente entre tarjetas */
  .kpi-col{ margin-bottom:22px; }
</style>

<style>
  /* contenedor central con tarjetas más anchas */
  .kpi-wrap{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;   /* centra horizontal */
    gap:24px;
    margin-inline:auto;
    max-width: 1400px;         /* límite para centrado bonito en pantallas grandes */
  }
  /* cada tarjeta ocupa más ancho */
  .kpi-col{
    flex: 1 1 360px;           /* mínimo ~420px, crece hasta llenar */
    max-width: 520px;          /* evita que sean gigantes en pantallas XL */
  }

  /* un poco más de presencia */
  .kpi-card{ min-height:180px; }
  .kpi-count{ font-size:52px; }
  .kpi-title{ font-size:20px; }
  .kpi-icon{ font-size:68px; }
</style>

        <div class="row">
        <h1>Panel de Control</h1>
        </div>
        <hr>

        <div class="row">
                {{-- Usuarios registrados --}}
                @can('menu.usuarios')
                <div class="col-12 col-md-6 col-xl-3 kpi-col">
                    <div class="kpi-card kpi-blue">
                    <div class="kpi-body">
                        <div>
                        <div class="kpi-count">{{ $usuarios->count() }}</div>
                        <div class="kpi-title">Usuarios registrados</div>
                        </div>
                        <div class="kpi-icon">
                        <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <a class="kpi-footer" href="{{ url('/admin/usuarios') }}">
                        <span>Más información</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>
                @endcan

                {{-- Delegados registradas --}}
                <div class="col-12 col-md-6 col-xl-3 kpi-col">
                    <div class="kpi-card kpi-green">
                    <div class="kpi-body">
                        <div>
                        <div class="kpi-count">{{ $miembros->count() }}</div>
                        <div class="kpi-title">Delegados registrados</div>
                        </div>
                        <div class="kpi-icon">
                        <i class="bi bi-person-raised-hand"></i>
                        </div>
                    </div>
                    <a class="kpi-footer" href="{{ url('/admin/postulaciones') }}">
                        <span>Más información</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>

                {{--Provincias registrados --}}
                <div class="col-12 col-md-6 col-xl-3 kpi-col">
                    <div class="kpi-card kpi-slate">
                    <div class="kpi-body">
                        <div>
                        <div class="kpi-count">{{ $provinces->count() }}</div>
                        <div class="kpi-title">Provincias registradas</div>
                        </div>
                        <div class="kpi-icon">
                        <i class="bi bi-map-fill"></i>
                        </div>
                    </div>
                    <a class="kpi-footer" href="{{ url('/admin/recintos/reporte') }}">
                        <span>Más información</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>

                
        </div>

        <div class="row">
                {{-- Municipios registradas --}}
                <div class="col-12 col-md-6 col-xl-3 kpi-col">
                    <div class="kpi-card kpi-indigo">
                    <div class="kpi-body">
                        <div>
                        <div class="kpi-count">{{ $municipalities->count() }}</div>
                        <div class="kpi-title">Municipios registrados</div>
                        </div>
                        <div class="kpi-icon">
                        <i class="bi bi-pin-map-fill"></i>
                        </div>
                    </div>
                    <a class="kpi-footer" href="{{ url('/reportes/mesas-por-municipio') }}">
                        <span>Más información</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>

                {{-- Recintos registradas --}}
                <div class="col-12 col-md-6 col-xl-3 kpi-col">
                    <div class="kpi-card kpi-morado">
                    <div class="kpi-body">
                        <div>
                        <div class="kpi-count">{{ $electoralprecincts->count() }}</div>
                        <div class="kpi-title">Recintos registrados</div>
                        </div>
                        <div class="kpi-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                        </div>
                    </div>
                    <a class="kpi-footer" href="{{ url('admin/recintos/reporte') }}">
                        <span>Más información</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>

                {{-- Mesas registrados --}}
                <div class="col-12 col-md-6 col-xl-3 kpi-col">
                    <div class="kpi-card kpi-rosado">
                    <div class="kpi-body">
                        <div>
                        <div class="kpi-count">{{ $tables->count() }}</div>
                        <div class="kpi-title">Mesas registradas</div>
                        </div>
                        <div class="kpi-icon">
                        <i class="bi bi-box-seam-fill"></i>
                        </div>
                    </div>
                    <a class="kpi-footer" href="{{ url('/admin/recintos/reporte') }}">
                        <span>Más información</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    </div>
                </div>
                
        </div>

        {{-- ===== Donuts de cobertura ===== --}}
<link rel="preconnect" href="https://cdn.jsdelivr.net" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
  .donut-card{ border:0; border-radius:16px; box-shadow:0 10px 24px rgba(0,0,0,.08); }
  .donut-wrap{ position:relative; width:100%; max-width:420px; margin-inline:auto; }
  .donut-center{
    position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:44px; color:#1e2a36;
  }
  .donut-title{ text-align:center; font-weight:700; margin-top:12px; font-size:18px; }
  .legend-mini{ display:flex; gap:18px; justify-content:center; margin-top:8px; font-weight:600;}
  .legend-dot{ width:14px; height:14px; border-radius:4px; display:inline-block; margin-right:6px; vertical-align:middle;}
  /* Colores */
  .c-oc{ background:#f5a623; }   /* ocupadas: dorado */
  .c-lib{ background:#cfd6dd; }  /* libres: gris */
  .c-rec{ background:#e4553f; }  /* recintos: coral */
</style>

<div class="row g-4 mt-2">
  {{-- Donut MESAS --}}
  <div class="col-12 col-lg-6">
    <div class="card donut-card">
      <div class="card-body">
        <div class="donut-wrap">
          <canvas id="donutMesas" height="260"></canvas>
          <div class="donut-center" id="labelMesas">{{ $metrics['mesas']['pct'] }}%</div>
        </div>
        <div class="donut-title">Cobertura de Mesas</div>
        <div class="legend-mini">
          <span><i class="legend-dot c-oc"></i>Ocupadas</span>
          <span><i class="legend-dot c-lib"></i>Libres</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Donut RECINTOS --}}
  <div class="col-12 col-lg-6">
    <div class="card donut-card">
      <div class="card-body">
        <div class="donut-wrap">
          <canvas id="donutRecintos" height="260"></canvas>
          <div class="donut-center" id="labelRecintos">{{ $metrics['recintos']['pct'] }}%</div>
        </div>
        <div class="donut-title">Cobertura de Recintos</div>
        <div class="legend-mini">
          <span><i class="legend-dot c-rec"></i>Ocupados</span>
          <span><i class="legend-dot c-lib"></i>Libres</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="text-center mt-2 text-muted">
  {{ $metrics['mesas']['cubiertas'] }} de {{ $metrics['mesas']['total'] }} mesas
</div>

<div class="text-center mt-2 text-muted">
  {{ $metrics['recintos']['cubiertos'] }} de {{ $metrics['recintos']['total'] }} recintos
</div>

<script>
(function(){
  const mesasPct    = {{ $metrics['mesas']['pct'] }};
  const recintosPct = {{ $metrics['recintos']['pct'] }};

  // Util: genera dataset [ocupadas, libres]
  const toDataset = (pct) => [pct, Math.max(0, 100 - pct)];

  // Estilo común
  const baseOptions = {
    type: 'doughnut',
    options: {
      cutout: '68%',
      radius: '92%',
      responsive: true,
      plugins: { legend: { display:false }, tooltip:{ enabled:true } },
    }
  };

  // Donut Mesas (dorado)
  new Chart(
    document.getElementById('donutMesas'),
    {
      ...baseOptions,
      data: {
        labels: ['Ocupadas','Libres'],
        datasets: [{
          data: toDataset(mesasPct),
          backgroundColor: ['#f5a623', '#cfd6dd'],
          borderWidth: 0
        }]
      }
    }
  );

  // Donut Recintos (coral)
  new Chart(
    document.getElementById('donutRecintos'),
    {
      ...baseOptions,
      data: {
        labels: ['Ocupados','Libres'],
        datasets: [{
          data: toDataset(recintosPct),
          backgroundColor: ['#e4553f', '#cfd6dd'],
          borderWidth: 0
        }]
      }
    }
  );
})();
</script>

{{-- SI QUIERES agregar tarjetas extra (Delegados, Municipios), duplica un bloque y cambia color/títulos/contadores. --}}
{{-- Ejemplo Delegados:
<div class="col-12 col-md-6 col-xl-3 kpi-col">
  <div class="kpi-card kpi-green">
    <div class="kpi-body">
      <div>
        <div class="kpi-count">{{ $miembros->count() }}</div>
        <div class="kpi-title">Delegados registrados</div>
      </div>
      <div class="kpi-icon"><i class="bi bi-person-arms-up"></i></div>
    </div>
    <a class="kpi-footer" href="{{ route('postulaciones.index') }}">
      <span>Más información</span><i class="fas fa-arrow-right"></i>
    </a>
  </div>
</div>
--}}

@endsection
