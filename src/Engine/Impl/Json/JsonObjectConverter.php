<?php

namespace Jabe\Engine\Impl\Json;

abstract class JsonObjectConverter
{
    public function toJson(\stdClass $object): string
    {
        return json_encode($this->toJsonObject($object));
    }

    abstract public function toJsonObject(\stdClass $object): \stdClass;

    abstract public function toObject(\stdClass $jsonString): \stdClass;
}
