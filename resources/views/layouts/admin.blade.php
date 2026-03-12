<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control Delegados </title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css')}}">

    <!-- icono de bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jquery -->
    <script src ="{{asset('/plugins/jquery/jquery.js')}}"></script>
   

      <!-- DataTables -->
  <link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">

  <!-- CSS de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- JS de Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  
 

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{url('/')}}" class="nav-link">Control Delegados</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">




            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                    <i class="fas fa-th-large"></i>
                </a>
            </li>

        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-
    info elevation-4">
        <!-- Brand Logo -->
        <a href="{{url('/')}}" class="brand-link">
            <img src="{{asset('dist/img/AdminLTELogo.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">PANEL</span>
        </a>

       <!-- Sidebar Menu -->
            <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-legacy clean" data-widget="treeview" role="menu" data-accordion="false">

                {{-- ====== Gestión ====== --}}
                <li class="nav-header">GESTIÓN</li>

                {{-- Roles --}}
                @can('menu.roles')
                @php $open = request()->routeIs('roles.*'); @endphp
                <li class="nav-item js-persistent-tree {{ $open ? 'menu-is-opening menu-open' : '' }}" data-menu-key="roles">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-shield"></i>
                    <p>Roles <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Listado de Roles</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('roles.create') }}" class="nav-link {{ request()->routeIs('roles.create') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Crear Rol</p>
                        </a>
                    </li>
                    </ul>
                </li>
                @endcan

                {{-- Permisos --}}
                @can('menu.permisos')
                @php $open = request()->routeIs('permissions.*'); @endphp
                <li class="nav-item js-persistent-tree {{ $open ? 'menu-is-opening menu-open' : '' }}" data-menu-key="permissions">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                    <i class="nav-icon fas fa-key"></i>
                    <p>Permisos <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.index') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Listado de Permisos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('permissions.create') }}" class="nav-link {{ request()->routeIs('permissions.create') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Crear Permiso</p>
                        </a>
                    </li>
                    </ul>
                </li>
                @endcan

                {{-- Usuarios --}}
                @can('menu.usuarios')
                @php $open = request()->routeIs('usuarios.*'); @endphp
                <li class="nav-item js-persistent-tree {{ $open ? 'menu-is-opening menu-open' : '' }}" data-menu-key="usuarios">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users"></i>
                    <p>Usuarios <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.index') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Listado de usuarios</p>
                        </a>
                    </li>
                    </ul>
                </li>
                @endcan


                {{-- ====== Operaciones ====== --}}
                <li class="nav-header">OPERACIONES</li>

                @can('menu.delegados')
                @php $open = request()->routeIs('delegados.*') || request()->routeIs('admin.delegados.*') || request()->routeIs('asignaciones.*') || request()->routeIs('postulaciones.*'); @endphp
                <li class="nav-item js-persistent-tree {{ $open ? 'menu-is-opening menu-open' : '' }}" data-menu-key="delegados">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                    <i class="nav-icon fas fa-id-card"></i>
                    <p>Delegados <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    @if(auth()->user()?->hasRole('admin'))
                    <li class="nav-item">
                        <a href="{{ route('admin.delegados.index') }}" class="nav-link {{ request()->routeIs('admin.delegados.index') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Listado de delegados</p>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ url('admin/delegados/create') }}" class="nav-link {{ request()->is('admin/delegados/create') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Crear delegado</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('postulaciones.index') }}" class="nav-link {{ request()->routeIs('postulaciones.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Lista de postulación</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('asignaciones.create') }}" class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Asignar delegado</p>
                        </a>
                    </li>
                    
                    </ul>
                </li>
                @endcan


                {{-- ====== Reportes ====== --}}
                <li class="nav-header">REPORTES</li>

                @php
                    $canReportes = auth()->check();
                @endphp
                @if($canReportes)
                @php $open = request()->routeIs('reportes.*') || request()->routeIs('asignados.*') || request()->routeIs('cobertura.*'); @endphp
                <li class="nav-item js-persistent-tree {{ $open ? 'menu-is-opening menu-open' : '' }}" data-menu-key="reportes">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p>Reportes <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    @can('menu.reportes')
                    <li class="nav-item">
                        <a href="{{ route('reportes.mesas_por_municipio') }}" class="nav-link {{ request()->routeIs('reportes.mesas_por_municipio') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Total Mesas por Recinto</p>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a href="{{ route('recintos.reporte') }}" class="nav-link {{ request()->routeIs('recintos.reporte') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Ver Recintos Electorales</p>
                        </a>
                    </li> --}}
                    <li class="nav-item">
                        <a href="{{ route('asignados.index') }}" class="nav-link {{ request()->routeIs('asignados.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Asignaciones realizadas</p>
                        </a>
                    </li>
                    @endcan
                    <li class="nav-item">
                    <a href="{{ route('cobertura.index') }}" class="nav-link {{ request()->routeIs('cobertura.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Cobertura de Mesas</p>
                    </a>
                    </li>
                    </ul>
                </li>
                @endif

                {{-- ====== Configuracion ====== --}}
                @php
                    $canConfig = auth()->check()
                        && auth()->user()->can('menu.configuracion')
                        && (auth()->user()->can('menu.importar_por_excel')
                            || auth()->user()->can('menu.actualizar_recintos_por_excel')
                            || auth()->user()->can('menu.actualizar_distritos_por_excel')
                            || auth()->user()->can('menu.datos_prueba'));
                @endphp
                @if($canConfig)
                <li class="nav-header">CONFIGURACION</li>
                @php $open = request()->routeIs('delegados.import.*') || request()->routeIs('recintos.excel.*') || request()->routeIs('distritos.excel.*') || request()->routeIs('staging.*'); @endphp
                <li class="nav-item js-persistent-tree {{ $open ? 'menu-is-opening menu-open' : '' }}" data-menu-key="configuracion">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                    <i class="nav-icon fas fa-cogs"></i>
                    <p>Configuracion <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    @can('menu.importar_por_excel')
                    <li class="nav-item">
                        <a href="{{ route('delegados.import.form') }}" class="nav-link {{ request()->routeIs('delegados.import.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Importar por Excel</p>
                        </a>
                    </li>
                    @endcan
                    @can('menu.actualizar_recintos_por_excel')
                    <li class="nav-item">
                        <a href="{{ route('recintos.excel.form') }}" class="nav-link {{ request()->routeIs('recintos.excel.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Actualizar recintos por Excel</p>
                        </a>
                    </li>
                    @endcan
                    @can('menu.actualizar_distritos_por_excel')
                    <li class="nav-item">
                        <a href="{{ route('distritos.excel.form') }}" class="nav-link {{ request()->routeIs('distritos.excel.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Actualizar distritos por Excel</p>
                        </a>
                    </li>
                    @endcan
                    @can('menu.datos_prueba')
                    <li class="nav-item">
                        <a href="{{ route('staging.index') }}" class="nav-link {{ request()->routeIs('staging.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-circle"></i><p>Staging: Datos de Prueba</p>
                        </a>
                    </li>
                    @endcan
                    </ul>
                </li>
                @endif

                {{-- ====== Auditorias ====== --}}
                @can('menu.auditorias')
                <li class="nav-header">AUDITORIAS</li>
                @php $open = request()->routeIs('reportes.*') || request()->routeIs('recintos.reporte') || request()->routeIs('asignados.*'); @endphp
                <li class="nav-item js-persistent-tree {{ $open ? 'menu-is-opening menu-open' : '' }}" data-menu-key="auditorias">
                    <a href="#" class="nav-link {{ $open ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p>Auditorias <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">

                    <li class="nav-item">
                        <a href="{{ route('actividad.index') }}" class="nav-link">
                            <i class="bi bi-clipboard-data"></i>
                            <p>Actividad del sistema</p>
                        </a>
                    </li>
                    
                    </ul>
                </li>
                @endcan

                {{-- ====== Sesión ====== --}}
                <li class="nav-header">CUENTA</li>
                <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="nav-icon fas fa-door-closed"></i>
                    <p>Cerrar sesión</p>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </li>

            </ul>
            </nav>
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <br>

        @if( ($message = Session::get('mensaje')) && ($icono = Session::get('icono')) )
            <script>

                Swal.fire({
                position: "top-end",
                icon: "{{$icono}}",
                title: "{{$message}}",
                showConfirmButton: false,
                timer: 3000
                });


                /* Swal.fire({
                    title: "Mensaje",
                    text: "{{$message}}",
                    icon: "{{$icono}}"
                }); */
            </script>
        @endif
            @if ($errors->any())
                <script>
                Swal.fire({
                icon: "error",
                title: "No se pudo guardar",
                html: `{!! implode('<br>', $errors->all()) !!}`
                });
                </script>
            @endif

 <!-- .content-wrapper -->
        <div class="container">
            @yield('content')
            @yield('scripts')
            
        </div>

    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    {{-- <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
        <div class="p-3">
            <h5>Title</h5>
            <p>Sidebar content</p>
        </div>
    </aside> --}}
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="float-right d-none d-sm-inline">
            Tarija - Bolivia
        </div>
        <!-- Default to the left -->
        <strong>Copyright &copy; 2026 <a href="">DJM</a>.</strong> V.2.0 Todos los derechos reservados.
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Bootstrap 4 -->
<script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('dist/js/adminlte.min.js')}}"></script>

<!-- DataTables  & Plugins -->
<script src="{{asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/jszip/jszip.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>


@stack('scripts')
<script>
  $(function () {
    const storageKey = 'admin-sidebar-open-menus';
    const $menus = $('.js-persistent-tree');

    function getStoredMenus() {
      try {
        const raw = localStorage.getItem(storageKey);
        const parsed = JSON.parse(raw || '[]');
        return Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        return [];
      }
    }

    function saveStoredMenus(keys) {
      localStorage.setItem(storageKey, JSON.stringify(keys));
    }

    function openMenu($item) {
      $item.addClass('menu-is-opening menu-open');
      $item.children('.nav-link').addClass('active');
      $item.children('.nav-treeview').stop(true, true).slideDown(150);
    }

    function closeMenu($item) {
      $item.removeClass('menu-is-opening menu-open');
      if (!$item.find('.nav-treeview .nav-link.active').length) {
        $item.children('.nav-link').removeClass('active');
      }
      $item.children('.nav-treeview').stop(true, true).slideUp(150);
    }

    const storedMenus = new Set(getStoredMenus());

    $menus.each(function () {
      const $item = $(this);
      const key = $item.data('menuKey');
      const hasActiveChild = $item.find('.nav-treeview .nav-link.active').length > 0;

      if (hasActiveChild) {
        storedMenus.add(key);
      }

      if (storedMenus.has(key)) {
        openMenu($item);
      } else {
        $item.removeClass('menu-is-opening menu-open');
        if (!$item.find('.nav-treeview .nav-link.active').length) {
          $item.children('.nav-link').removeClass('active');
        }
        $item.children('.nav-treeview').hide();
      }
    });

    saveStoredMenus(Array.from(storedMenus));

    $menus.children('.nav-link').on('click', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();

      const $item = $(this).closest('.js-persistent-tree');
      const key = $item.data('menuKey');
      const currentMenus = new Set(getStoredMenus());

      if ($item.hasClass('menu-open')) {
        closeMenu($item);
        currentMenus.delete(key);
      } else {
        openMenu($item);
        currentMenus.add(key);
      }

      saveStoredMenus(Array.from(currentMenus));
    });
  });
</script>
<style>
  /* ====== Sidebar Clean (Opción C) ====== */
  :root{
    --menu-accent: #3b82f6; /* azul acento */
    --menu-hover: rgba(59,130,246, .08);
    --menu-active: rgba(59,130,246, .15);
    --menu-text: #e5e7eb;
    --menu-muted: #9ca3af;
  }
  .main-sidebar { background: #111827; } /* gris muy oscuro */
  .brand-link { border-bottom: 1px solid #1f2937; }
  .user-panel { border-bottom: 1px dashed #1f2937; }

  .nav-sidebar.clean > .nav-header{
    font-size:.75rem; letter-spacing:.08em; color:var(--menu-muted);
    margin:14px 12px 6px; padding-left:6px;
  }
  .nav-sidebar.clean .nav-item > .nav-link{
    margin: 3px 8px; border-radius: 10px; color: var(--menu-text);
    position: relative; transition: .2s ease;
  }
  .nav-sidebar.clean .nav-item > .nav-link .right{ opacity:.5; }
  .nav-sidebar.clean .nav-item > .nav-link:hover{
    background: var(--menu-hover); color:#fff;
  }
  .nav-sidebar.clean .nav-item.menu-open > .nav-link,
  .nav-sidebar.clean .nav-item > .nav-link.active{
    background: var(--menu-active); color:#fff; box-shadow: inset 0 0 0 1px rgba(59,130,246,.15);
  }
  /* Acento lateral */
  .nav-sidebar.clean .nav-item > .nav-link.active::before,
  .nav-sidebar.clean .nav-item.menu-open > .nav-link::before{
    content:""; position:absolute; left:-4px; top:8px; bottom:8px; width:3px; border-radius:3px;
    background: linear-gradient(180deg, var(--menu-accent), #8b5cf6);
  }
  /* Submenú */
  .nav-sidebar.clean .nav-treeview .nav-link{ margin:3px 14px; border-radius:8px; }
  .nav-sidebar.clean .nav-treeview .nav-link.active{ background: var(--menu-active); }
  .nav-sidebar.clean .nav-treeview .nav-link .nav-icon{ font-size:.85rem; opacity:.8; }
  /* Iconos */
  .nav-sidebar.clean .nav-icon{ width: 22px; text-align:center; margin-right:10px; }
  /* User mini-card */
  .user-panel .info a{ color:#f3f4f6; font-weight:600; }
  .user-panel .image img{ border:2px solid #1f2937; }
</style>

</body>
</html>
