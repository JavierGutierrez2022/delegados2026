<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionNames = [
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
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
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::whereIn('name', $permissionNames)->get());

        $moderador = Role::firstOrCreate(['name' => 'moderador', 'guard_name' => 'web']);
        $moderador->syncPermissions([
            'ver usuarios',
            'editar usuarios',
            'menu.usuarios',
            'menu.delegados',
            'menu.reportes',
            'menu.cobertura_mesas',
            'menu.auditorias',
        ]);

        $usuario = Role::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
        $usuario->syncPermissions([
            'menu.delegados',
            'menu.reportes',
            'menu.cobertura_mesas',
        ]);
    }
}
