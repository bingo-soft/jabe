<?php

namespace Jabe\Impl\Json;

abstract class JsonArrayConverter
{
    public function toJson(\stdClass $object): string
    {
        return json_encode($this->toJsonArray($object));
    }

    abstract public function toJsonArray(\stdClass $object): array;

    abstract public function toObject(array $jsonArray): \stdClass;
}
