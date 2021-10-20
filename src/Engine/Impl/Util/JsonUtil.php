<?php

namespace BpmPlatform\Engine\Impl\Util;

class JsonUtil
{
    public static function asString(?array $properties = null): string
    {
        if (!empty($properties)) {
            return json_encode($properties);
        }
        return "";
    }
}
