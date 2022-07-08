<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\{
    QueryOperator,
    TaskQueryVariableValue
};
use Jabe\Engine\Impl\Util\JsonUtil;

class JsonTaskQueryVariableValueConverter extends JsonObjectConverter
{
    public function toJsonObject(/*TaskQueryVariableValue*/$object, bool $isOrQueryActive = false): ?\stdClass
    {
        $jsonObject = JsonUtil::createObject();
        JsonUtil::addField($jsonObject, "name", $variable->getName());
        JsonUtil::addFieldRawValue($jsonObject, "value", $variable->getValue());
        JsonUtil::addField($jsonObject, "operator", $variable->getOperator());
        return $jsonObject;
    }

    public function toObject(\stdClass $jsonString, bool $isOrQuery = false)
    {
        $name = JsonUtil::getString($json, "name");
        $value = JsonUtil::getRawObject($json, "value");
        $operator = constant("QueryOperator::", JsonUtil::getString($json, "operator"));
        $isTaskVariable = JsonUtil::getBoolean($json, "taskVariable");
        $isProcessVariable = JsonUtil::getBoolean($json, "processVariable");
        return new TaskQueryVariableValue($name, $value, $operator, $isTaskVariable, $isProcessVariable);
    }
}
