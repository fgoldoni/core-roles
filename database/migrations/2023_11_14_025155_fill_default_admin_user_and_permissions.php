<?php

use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class FillDefaultAdminUserAndPermissions
 */
class FillDefaultAdminUserAndPermissions extends Migration
{
    /**
     * @var Repository|mixed
     */
    protected $guardName;
    /**
     * @var mixed
     */
    protected $userClassName;
    /**
     * @var
     */
    protected $userTable;

    /**
     * @var array
     */
    protected $permissions;
    /**
     * @var array
     */
    protected $roles;
    /**
     * @var array
     */
    protected $users;

    /**
     * @var string
     */
    protected $password = 'support@sell-first.com';

    /**
     * FillDefaultAdminUserAndPermissions constructor.
     */
    public function __construct()
    {
        $this->guardName = config('core-roles.defaults.guard');
        $providerName = config('auth.guards.' . $this->guardName . '.provider');
        $provider = config('auth.providers.' . $providerName);
        if ($provider['driver'] === 'eloquent') {
            $this->userClassName = $provider['model'];
        }
        $this->userTable = (new $this->userClassName())->getTable();

        $defaultPermissions = collect(config('core-roles.custom.system'));
        $defaultRoles = collect(config('core-roles.roles'));

        //Add new permissions
        $this->permissions = $defaultPermissions->map(fn ($permission) => [
            'id' => (string) \Illuminate\Support\Str::ulid(),
            'name' => $permission,
            'guard_name' => $this->guardName,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ])->toArray();

        //Add new roles
        $this->roles = $defaultRoles->map(fn ($role) => [
            'id' => (string) \Illuminate\Support\Str::ulid(),
            'name' => $role,
            'guard_name' => $this->guardName,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ])->toArray();

        //Add new users
        $this->users = [
            [
                'id' => \Illuminate\Support\Str::ulid(),
                'name' => 'Admin SG',
                'email' => config('core-roles.developer_email'),
                'password' => Hash::make(config('core-roles.developer_password')),
                'email_verified_at' => now(),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'roles' => [
                    [
                        'name' => config('core-roles.roles.administrator'),
                        'guard_name' => $this->guardName,
                    ],
                ],
                'permissions' => [
                    //
                ]
            ],
        ];
    }

    /**
     * Run the migrations.
     *
     * @throws Exception
     * @return void
     */
    public function up(): void
    {
        if ($this->userClassName === null) {
            throw new RuntimeException('Admin user model not defined');
        }
        DB::transaction(function () {
            $columnNames = config('permission.column_names');
            foreach ($this->permissions as $permission) {
                $permissionItem = DB::table('permissions')->where([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name']
                ])->first();
                if ($permissionItem === null) {
                    DB::table('permissions')->insert($permission);
                }
            }

            foreach ($this->roles as $role) {

                $roleItem = DB::table('roles')->where([
                    'name' => $role['name'],
                    'guard_name' => $role['guard_name']
                ])->first();

                if ($roleItem === null) {
                    DB::table('roles')->insert($role);
                    $roleId = DB::table('roles')->where([
                        'name' => $role['name'],
                        'guard_name' => $role['guard_name']
                    ])->first()->id;
                } else {
                    $roleId = $roleItem->id;
                }

                if ($role['name'] === config('core-roles.roles.administrator')) {
                    foreach (DB::table('permissions')->get() as $permissionItem) {
                        $roleHasPermissionData = [
                            'permission_id' => $permissionItem->id,
                            'role_id' => $roleId
                        ];
                        $roleHasPermissionItem = DB::table('role_has_permissions')->where($roleHasPermissionData)->first();
                        if ($roleHasPermissionItem === null) {
                            DB::table('role_has_permissions')->insert($roleHasPermissionData);
                        }
                    }
                }
            }

            foreach ($this->users as $user) {
                $roles = $user['roles'];
                unset($user['roles']);

                $permissions = $user['permissions'];
                unset($user['permissions']);

                $userItem = DB::table($this->userTable)->where([
                    'email' => $user['email'],
                ])->first();

                if ($userItem === null) {
                    DB::table($this->userTable)->insert($user);

                    $userId = DB::table($this->userTable)->where([
                        'email' => $user['email']
                    ])->first()->id;

                    DB::table('teams')->insert(
                        [
                            'id' => (string) \Illuminate\Support\Str::ulid(),
                            'name' => $user['name'] . '\'s Team',
                            'user_id' => $userId,
                            'personal_team' => true,
                        ]
                    );


                    foreach ($roles as $role) {
                        $roleItem = DB::table('roles')->where([
                            'name' => $role['name'],
                            'guard_name' => $role['guard_name']
                        ])->first();

                        $modelHasRoleData = [
                            'role_id' => $roleItem->id,
                            $columnNames['model_morph_key'] => $userId,
                            'model_type' => $this->userClassName
                        ];
                        $modelHasRoleItem = DB::table('model_has_roles')->where($modelHasRoleData)->first();
                        if ($modelHasRoleItem === null) {
                            DB::table('model_has_roles')->insert($modelHasRoleData);
                        }
                    }

                    foreach ($permissions as $permission) {
                        $permissionItem = DB::table('permissions')->where([
                            'name' => $permission['name'],
                            'guard_name' => $permission['guard_name']
                        ])->first();

                        $modelHasPermissionData = [
                            'permission_id' => $permissionItem->id,
                            $columnNames['model_morph_key'] => $userId,
                            'model_type' => $this->userClassName
                        ];
                        $modelHasPermissionItem = DB::table('model_has_permissions')->where($modelHasPermissionData)->first();
                        if ($modelHasPermissionItem === null) {
                            DB::table('model_has_permissions')->insert($modelHasPermissionData);
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
     * @throws Exception
     * @return void
     */
    public function down(): void
    {
        if ($this->userClassName === null) {
            throw new RuntimeException('Admin user model not defined');
        }
        DB::transaction(function () {
            $columnNames = config('permission.column_names');
            foreach ($this->users as $user) {
                $userItem = DB::table($this->userTable)->where('email', $user['email'])->first();
                if ($userItem !== null) {
                    DB::table($this->userTable)->where('id', $userItem->id)->delete();
                    DB::table('model_has_permissions')->where([
                        $columnNames['model_morph_key'] => $userItem->id,
                        'model_type' => $this->userClassName
                    ])->delete();
                    DB::table('model_has_roles')->where([
                        $columnNames['model_morph_key'] => $userItem->id,
                        'model_type' => $this->userClassName
                    ])->delete();
                }
            }

            foreach ($this->roles as $role) {
                $roleItem = DB::table('roles')->where([
                    'name' => $role['name'],
                    'guard_name' => $role['guard_name']
                ])->first();
                if ($roleItem !== null) {
                    DB::table('roles')->where('id', $roleItem->id)->delete();
                    DB::table('model_has_roles')->where('role_id', $roleItem->id)->delete();
                }
            }

            foreach ($this->permissions as $permission) {
                $permissionItem = DB::table('permissions')->where([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name']
                ])->first();
                if ($permissionItem !== null) {
                    DB::table('permissions')->where('id', $permissionItem->id)->delete();
                    DB::table('model_has_permissions')->where('permission_id', $permissionItem->id)->delete();
                }
            }
        });
        app()['cache']->forget(config('permission.cache.key'));
    }
}
