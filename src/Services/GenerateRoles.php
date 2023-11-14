<?php

namespace Goldoni\CoreRoles\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use TomatoPHP\ConsoleHelpers\Traits\HandleStub;

class GenerateRoles
{
    use HandleStub;

    /**
     * @var string[]
     */
    private readonly array $permissions;

    private readonly string $stubPath;

    public function __construct(private readonly string $tableName)
    {
        $this->permissions = [
            Str::replace('_', '-', $tableName) .'.index',
            Str::replace('_', '-', $tableName) .'.api',
            Str::replace('_', '-', $tableName) .'.create',
            Str::replace('_', '-', $tableName) .'.store',
            Str::replace('_', '-', $tableName) .'.show',
            Str::replace('_', '-', $tableName) .'.edit',
            Str::replace('_', '-', $tableName) .'.update',
            Str::replace('_', '-', $tableName) .'.destroy',
            Str::replace('_', '-', $tableName) .'.export',
            Str::replace('_', '-', $tableName) .'.bulk',
        ];

        $this->stubPath = __DIR__ . '/../../stubs/';
    }

    /**
     * @return void
     */
    public function generate(): void
    {
        $this->generatePermissionsMigration();
        Artisan::call('migrate');
    }


    /**
     * @return void
     */
    private function generatePermissionsMigration(): void
    {
        $permissions = "";
        foreach ($this->permissions as $per) {
            $permissions .= '"'.$per.'",' . "\n";
        }
        $this->generateStubs(
            $this->stubPath . "migration.stub",
            database_path("migrations/".date('Y_m_d_His')."_fill_permissions_for_".$this->tableName.".php"),
            [
                "name" => Str::camel(Str::replace('_', ' ', $this->tableName)),
                "permissions" => $permissions
            ],
            [
                database_path("migrations")
            ]
        );
    }
}
