<?php

namespace Jabe\Impl\Batch\Update;

use Jabe\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Util\JsonUtil;

class UpdateProcessInstancesSuspendStateBatchConfigurationJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;// = new UpdateProcessInstancesSuspendStateBatchConfigurationJsonConverter();

    public const PROCESS_INSTANCE_IDS = "processInstanceIds";
    public const PROCESS_INSTANCE_ID_MAPPINGS = "processInstanceIdMappings";
    public const SUSPENDING = "suspended";

    public static function instance(): JsonObjectConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new UpdateProcessInstancesSuspendStateBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*UpdateProcessInstancesSuspendStateBatchConfiguration*/$configuration, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addField($json, self::SUSPENDING, $configuration->getSuspended());
        return $json;
    }

    public function toObject(\stdClass $json, bool $isOrQuery = false)
    {
        $configuration =
            new UpdateProcessInstancesSuspendStateBatchConfiguration(
                $this->readProcessInstanceIds($json),
                $this->readMappings($json),
                JsonUtil::getBoolean($json, self::SUSPENDING)
            );

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
