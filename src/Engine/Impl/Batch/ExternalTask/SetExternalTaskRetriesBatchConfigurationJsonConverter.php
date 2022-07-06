<?php

namespace Jabe\Engine\Impl\Batch\ExternalTask;

use Jabe\Engine\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings,
    SetRetriesBatchConfiguration
};
use Jabe\Engine\Impl\Json\JsonObjectConverter;
use Jabe\Engine\Impl\Util\JsonUtil;

class SetExternalTaskRetriesBatchConfigurationJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;
    public const EXTERNAL_TASK_IDS = "externalTaskIds";
    public const EXTERNAL_TASK_ID_MAPPINGS = "externalTaskIdMappingss";
    public const RETRIES = "retries";

    public static function instance(): SetExternalTaskRetriesBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new SetExternalTaskRetriesBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject($configuration): \stdClass
    {
        $json = JsonUtil::createObject();
        JsonUtil::addListField($json, self::EXTERNAL_TASK_IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::EXTERNAL_TASK_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addField($json, self::RETRIES, $configuration->getRetries());
        return $json;
    }

    public function toObject(\stdClass $json)
    {
        return new SetRetriesBatchConfiguration($this->readExternalTaskIds($json), $this->readIdMappings($json), JsonUtil::getInt($json, self::RETRIES));
    }

    protected function readExternalTaskIds(\stdClass $json): array
    {
        return JsonUtil::asStringList(JsonUtil::getArray($json, self::EXTERNAL_TASK_IDS));
    }

    protected function readIdMappings(\stdClass $json): DeploymentMappings
    {
        return JsonUtil::asList(JsonUtil::getArray($json, self::EXTERNAL_TASK_ID_MAPPINGS), DeploymentMappingJsonConverter::instance(), DeploymentMappings::class);
    }
}
