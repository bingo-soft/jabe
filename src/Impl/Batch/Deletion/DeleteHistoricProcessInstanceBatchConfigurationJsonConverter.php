<?php

namespace Jabe\Impl\Batch\Deletion;

use Jabe\Impl\Batch\{
    BatchConfiguration,
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Util\JsonUtil;

class DeleteHistoricProcessInstanceBatchConfigurationJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;

    public const HISTORIC_PROCESS_INSTANCE_IDS = "historicProcessInstanceIds";
    public const HISTORIC_PROCESS_INSTANCE_ID_MAPPINGS = "historicProcessInstanceIdMappings";
    public const FAIL_IF_NOT_EXISTS = "failIfNotExists";

    public static function instance(): DeleteHistoricProcessInstanceBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new DeleteHistoricProcessInstanceBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject($configuration): \stdClass
    {
        $json = JsonUtil::createObject();
        JsonUtil::addListField($json, self::HISTORIC_PROCESS_INSTANCE_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addListField($json, self::HISTORIC_PROCESS_INSTANCE_IDS, $configuration->getIds());
        JsonUtil::addField($json, self::FAIL_IF_NOT_EXISTS, $configuration->isFailIfNotExists());
        return $json;
    }

    public function toObject(\stdClass $json)
    {
        $configuration = new BatchConfiguration(
            $this->readProcessInstanceIds($json),
            $this->readIdMappings($json),
            JsonUtil::getBoolean($json, self::FAIL_IF_NOT_EXISTS)
        );
        return $configuration;
    }

    protected function readProcessInstanceIds(\stdClass $jsonObject): array
    {
        return JsonUtil::asStringList(JsonUtil::getArray($jsonObject, self::HISTORIC_PROCESS_INSTANCE_IDS));
    }

    protected function readIdMappings(\stdClass $json): DeploymentMappings
    {
        return JsonUtil::asList(JsonUtil::getArray($json, self::HISTORIC_PROCESS_INSTANCE_ID_MAPPINGS), DeploymentMappingJsonConverter::instance(), DeploymentMappings::class);
    }
}
