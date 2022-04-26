<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\Util\JsonUtil;

class JsonArrayOfObjectsConverter extends JsonArrayConverter
{
    protected $objectConverter;

    public function __construct(JsonObjectConverter $objectConverter)
    {
        $this->objectConverter = $objectConverter;
    }

    public function toJsonArray(array $objects): array
    {
        $jsonArray = JsonUtil::createArray();

        foreach ($objects as $object) {
            $jsonObject = $this->objectConverter->toJsonObject($object);
            $jsonArray[] = $jsonObject;
        }

        return $jsonArray;
    }

    public function toObject(array $jsonArray): array
    {
        $result = [];
        foreach ($jsonArray as $jsonElement) {
            $object = $this->objectConverter->toObject(JsonUtil::getObject($jsonElement));
            $result[] = $object;
        }

        return $result;
    }
}
