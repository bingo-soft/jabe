<?php

namespace Jabe\Authorization;

use Jabe\Authorization\Exception\PermissionNotFound;

abstract class AbstractPermissions implements PermissionInterface
{
    use PermissionTrait;

    public function __construct(string $name)
    {
        $ref = new \ReflectionClass(__CLASS__);
        $constants = $ref->getConstants();
        if (array_search($name, $constants) !== false) {
            $this->name = $name;
            $i = 0;
            foreach ($constants as $key => $value) {
                if ($value == $name) {
                    $this->id = $i;
                    break;
                }
                $i += 1;
            }
        } else {
            throw new PermissionNotFound(sprintf("Permission %s not found", $name));
        }
    }
}
