<?php

namespace Jabe\Impl\Batch\Variables;

use Jabe\Impl\Batch\{
    BatchConfiguration,
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Util\JsonUtil;

class SetVariablesJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;

    protected const IDS = "ids";
    protected const ID_MAPPINGS = "idMappings";
    protected const BATCH_ID = "batchId";

    public function instance(): JsonObjectConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new SetVariablesJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*BatchConfiguration*/$configuration, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();

        JsonUtil::addListField($json, self::IDS, $configuration->getIds());
        JsonUtil::addListField(
            $json,
            self::ID_MAPPINGS,
            DeploymentMappingJsonConverter::instance(),
            $configuration->getIdMappings()
        );
        JsonUtil::addField($json, self::BATCH_ID, $configuration->getBatchId());

        return $json;
    }

    public function toObject(\stdClass $jsonObject, bool $isOrQuery = false)
    {
        $instanceIds = JsonUtil::asStringList(JsonUtil::getArray($jsonObject, self::IDS));

        $mappings = JsonUtil::asList(
            JsonUtil::getArray($jsonObject, self::ID_MAPPINGS),
            DeploymentMappingJsonConverter::instance(),
            DeploymentMappings::class
        );

        $batchId = JsonUtil::getString($jsonObject, self::BATCH_ID);

        return new BatchConfiguration($instanceIds, $mappings, $batchId);
    }
}
