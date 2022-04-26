<?php

namespace Jabe\Engine\Impl\Cmd;

class ProcessDefinitionGroup
{
    public $key;
    public $tenant;
    public $processDefinitions = [];

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj == null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->key == null) {
            if ($obj->key != null) {
                return false;
            }
        } elseif ($this->key != $obj->key) {
            return false;
        }
        if ($this->tenant == null) {
            if ($obj->tenant != null) {
                return false;
            }
        } elseif ($this->tenant != $obj->tenant) {
            return false;
        }
        return true;
    }

    public function __toString()
    {
        return $this->key . '_' . $this->tenant;
    }
}
