<?php

namespace Goldoni\CoreRoles\Services;

class Permission
{
    public string $group;


    public string $name;


    public string $guard;


    public static function make(): static
    {
        return (new static);
    }


    public function toArray(): array
    {
        return array([
            "group" => $this->group ?? null,
            "name" => $this->name ?? null,
            "guard_name" => $this->guard ?? null,
        ]);
    }



    public function group(string $group): static
    {
        $this->group = $group;
        return $this;
    }


    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }


    public function guard(string $guard): static
    {
        $this->guard = $guard;
        return $this;
    }
}
