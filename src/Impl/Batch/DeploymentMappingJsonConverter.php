<?php

namespace Jabe\Impl\Batch;

use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Util\JsonUtil;

class DeploymentMappingJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;

    protected const COUNT = "count";
    protected const DEPLOYMENT_ID = "deploymentId";

    public function instance(): JsonObjectConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new DeploymentMappingJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*BatchConfiguration*/$mapping, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();
        $json->addProperty(self::DEPLOYMENT_ID, $mapping->getDeploymentId());
        $json->addProperty(self::COUNT, $mapping->getCount());
        return $json;
    }

    public function toObject(\stdClass $jsonObject, bool $isOrQuery = false)
    {
        $deploymentId = JsonUtil::isNull($jsonObject, self::DEPLOYMENT_ID) ? null : JsonUtil::getString($jsonObject, self::DEPLOYMENT_ID);
        $count = JsonUtil::getInt($jsonObject, self::COUNT);
        return new DeploymentMapping($deploymentId, $count);
    }
}
