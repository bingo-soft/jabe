<?php

namespace Jabe\Impl\Json;

abstract class JsonObjectConverter
{
    public function toJson($object): string
    {
        return json_encode($this->toJsonObject($object));
    }

    abstract public function toJsonObject($object, bool $isOrQueryActive = false): ?\stdClass;

    abstract public function toObject(\stdClass $jsonString, bool $isOrQuery = false);
}
