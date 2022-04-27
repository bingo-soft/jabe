<?php

namespace Jabe\Engine\Impl\Json;

abstract class JsonObjectConverter
{
    public function toJson($object): string
    {
        return json_encode($this->toJsonObject($object));
    }

    abstract public function toJsonObject($object): ?\stdClass;

    abstract public function toObject(\stdClass $jsonString);
}
