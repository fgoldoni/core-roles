<?php

namespace Goldoni\CoreRoles\Services;

class CoreRoles
{
    /**
     * @var array
     */
    public static array $permission = [];

    /**
     * @param \Goldoni\CoreRoles\Services\Permission $item
     * @return void
     */
    public static function register(Permission $item): void
    {
        self::$permission[] = $item;
    }

    /**
     * @return array
     */
    public static function loadPermission(): array
    {
        $get = self::$permission;
        $returnItems = [];
        foreach ($get as $item) {
            $returnItems[$item->group][] = [
                'name' => $item->name,
                'guard_name' => $item->guard,
                'table' => $item->group,
            ];
        }
        return $returnItems;
    }
}
