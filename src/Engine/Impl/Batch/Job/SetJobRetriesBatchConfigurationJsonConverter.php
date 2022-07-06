<?php

namespace Jabe\Engine\Impl\Batch\ExternalTask;

use Jabe\Engine\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings,
    SetRetriesBatchConfiguration
};
use Jabe\Engine\Impl\Json\JsonObjectConverter;
use Jabe\Engine\Impl\Util\JsonUtil;

class SetJobRetriesBatchConfigurationJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;

    public const JOB_IDS = "jobIds";
    public const JOB_ID_MAPPINGS = "jobIdMappings";
    public const RETRIES = "retries";

    public static function instance(): SetJobRetriesBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new SetJobRetriesBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject($configuration): \stdClass
    {
        $json = JsonUtil::createObject();
        JsonUtil::addListField($json, self::JOB_IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::JOB_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addField($json, self::RETRIES, $configuration->getRetries());
        return $json;
    }

    public function toObject(\stdClass $json)
    {
        $configuration = new SetRetriesBatchConfiguration(
            $this->readJobIds($json),
            $this->readIdMappings($json),
            JsonUtil::getInt($json, self::RETRIES)
        );
        return $configuration;
    }

    protected function readJobIds(\stdClass $jsonObject): array
    {
        return JsonUtil::asStringList(JsonUtil::getArray($jsonObject, self::JOB_IDS));
    }

    protected function readIdMappings(\stdClass $jsonObject): DeploymentMappings
    {
        return JsonUtil::asList(JsonUtil::getArray($jsonObject, self::JOB_ID_MAPPINGS), DeploymentMappingJsonConverter::instance(), DeploymentMappings::class);
    }
}
