<?php

namespace Jabe\Impl\Batch\Deletion;

use Jabe\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Util\JsonUtil;

class DeleteProcessInstanceBatchConfigurationJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;
    public const DELETE_REASON = "deleteReason";
    public const PROCESS_INSTANCE_IDS = "processInstanceIds";
    public const PROCESS_INSTANCE_ID_MAPPINGS = "processInstanceIdMappings";
    public const SKIP_CUSTOM_LISTENERS = "skipCustomListeners";
    public const SKIP_SUBPROCESSES = "skipSubprocesses";
    public const FAIL_IF_NOT_EXISTS = "failIfNotExists";

    public static function instance(): DeleteProcessInstanceBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new DeleteProcessInstanceBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(DeleteProcessInstanceBatchConfiguration $configuration): \stdClass
    {
        $json = JsonUtil::createObject();
        JsonUtil::addField($json, self::DELETE_REASON, $configuration->getDeleteReason());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_IDS, $configuration->getIds());
        JsonUtil::addField($json, self::SKIP_CUSTOM_LISTENERS, $configuration->isSkipCustomListeners());
        JsonUtil::addField($json, self::SKIP_SUBPROCESSES, $configuration->isSkipSubprocesses());
        JsonUtil::addField($json, self::FAIL_IF_NOT_EXISTS, $configuration->isFailIfNotExists());
        return $json;
    }

    public function toObject(\stdClass $json)
    {
        $configuration =
            new DeleteProcessInstanceBatchConfiguration(
                $this->readProcessInstanceIds($json),
                $this->readIdMappings($json),
                null,
                JsonUtil::getBoolean($json, self::SKIP_CUSTOM_LISTENERS),
                JsonUtil::getBoolean($json, self::SKIP_SUBPROCESSES),
                JsonUtil::getBoolean($json, self::FAIL_IF_NOT_EXISTS)
            );

        $deleteReason = JsonUtil::getString($json, self::DELETE_REASON);

        if (!empty($deleteReason)) {
            $configuration->setDeleteReason($deleteReason);
        }

        return $configuration;
    }

    protected function readProcessInstanceIds(\stdClass $jsonObject): array
    {
        return JsonUtil::asStringList(JsonUtil::getArray($jsonObject, self::PROCESS_INSTANCE_IDS));
    }

    protected function readIdMappings(\stdClass $jsonObject): DeploymentMappings
    {
        return JsonUtil::asList(JsonUtil::getArray($jsonObject, self::PROCESS_INSTANCE_ID_MAPPINGS), DeploymentMappingJsonConverter::instance(), DeploymentMappings::class);
    }
}
