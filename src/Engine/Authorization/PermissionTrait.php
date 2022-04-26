<?php

namespace Jabe\Engine\Authorization;

trait PermissionTrait
{
    private $name;
    private $id;

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): int
    {
        return $this->id;
    }

    public static function forName(string $name): PermissionInterface
    {
        $names = explode("_", strtolower($name));
        $func = $names[0];
        if (count($names) > 1) {
            for ($i = 1; $i < count($names); $i += 1) {
                $func .= ucfirst($names[$i]);
            }
        }

        return self::$func();
    }
}
