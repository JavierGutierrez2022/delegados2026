<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MenuPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'menu.roles',
            'menu.permisos',
            'menu.usuarios',
            'menu.delegados',
            'menu.reportes',
            'menu.cobertura_mesas',
            'menu.configuracion',
            'menu.importar_por_excel',
            'menu.actualizar_recintos_por_excel',
            'menu.actualizar_distritos_por_excel',
            'menu.datos_prueba',
            'menu.auditorias',
        ] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
