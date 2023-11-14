<?php

use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FillPermissionsForusers extends Migration
{

    protected string $guardName;

    protected array $permissions;

    protected array $roles;


    public function __construct()
    {
        $this->guardName = config('core-roles.defaults.guard');

        $permissions = collect(config('core-roles.defaults.permissions.users'));

        $this->permissions = $permissions->map(fn ($permission) => [
            'id' => (string) \Illuminate\Support\Str::ulid(),
            'name' => $permission,
            'guard_name' => $this->guardName,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ])->toArray();

        $this->roles = [
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => config('core-roles.roles.administrator'),
                'guard_name' => $this->guardName,
                'permissions' => $permissions,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => config('core-roles.roles.manager'),
                'guard_name' => $this->guardName,
                'permissions' => [],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => config('core-roles.roles.user'),
                'guard_name' => $this->guardName,
                'permissions' => [],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }


    public function up(): void
    {
        $tableNames = config('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);

        DB::transaction(function () use ($tableNames) {
            foreach ($this->permissions as $permission) {
                $permissionItem = DB::table($tableNames['permissions'])->where([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name']
                ])->first();
                if ($permissionItem === null) {
                    DB::table($tableNames['permissions'])->insert($permission);
                }
            }

            foreach ($this->roles as $role) {
                $permissions = $role['permissions'];
                unset($role['permissions']);

                $roleItem = DB::table($tableNames['roles'])->where([
                    'name' => $role['name'],
                    'guard_name' => $role['guard_name']
                ])->first();
                if ($roleItem !== null) {
                    $roleId = $roleItem->id;

                    $permissionItems = DB::table($tableNames['permissions'])->whereIn('name', $permissions)->where(
                        'guard_name',
                        $role['guard_name']
                    )->get();
                    foreach ($permissionItems as $permissionItem) {
                        $roleHasPermissionData = [
                            'permission_id' => $permissionItem->id,
                            'role_id' => $roleId
                        ];
                        $roleHasPermissionItem = DB::table($tableNames['role_has_permissions'])->where($roleHasPermissionData)->first();
                        if ($roleHasPermissionItem === null) {
                            DB::table($tableNames['role_has_permissions'])->insert($roleHasPermissionData);
                        }
                    }
                }
            }
        });
        app()['cache']->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);

        DB::transaction(function () use ($tableNames) {
            foreach ($this->permissions as $permission) {
                $permissionItem = DB::table($tableNames['permissions'])->where([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name']
                ])->first();
                if ($permissionItem !== null) {
                    DB::table($tableNames['permissions'])->where('id', $permissionItem->id)->delete();
                    DB::table($tableNames['model_has_permissions'])->where('permission_id', $permissionItem->id)->delete();
                }
            }
        });
        app()['cache']->forget(config('permission.cache.key'));
    }
}
