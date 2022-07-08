<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Engine\Impl\Batch\Message\MessageCorrelationBatchConfiguration;
use Jabe\Engine\Impl\Util\JsonUtil;

class MessageCorrelationBatchConfigurationJsonConverter extends JsonObjectConverter
{
    private static $INSTANCE;
    public const MESSAGE_NAME = "messageName";
    public const PROCESS_INSTANCE_IDS = "processInstanceIds";
    public const PROCESS_INSTANCE_ID_MAPPINGS = "processInstanceIdMappings";
    public const BATCH_ID = "batchId";

    public static function instance(): MessageCorrelationBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new MessageCorrelationBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*(MessageCorrelationBatchConfiguration*/$configuration, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();

        JsonUtil::addField($json, self::MESSAGE_NAME, $configuration->getMessageName());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addField($json, self::BATCH_ID, $configuration->getBatchId());

        return $json;
    }

    public function toObject(\stdClass $json, bool $isOrQuery = false)
    {
        return new MessageCorrelationBatchConfiguration(
            $this->readProcessInstanceIds($json),
            $this->readIdMappings($json),
            JsonUtil::getString($json, self::MESSAGE_NAME, null),
            JsonUtil::getString($json, self::BATCH_ID)
        );
    }

    protected function readProcessInstanceIds($jsonObject): array
    {
        return JsonUtil::asStringList(JsonUtil::getArray($jsonObject, self::PROCESS_INSTANCE_IDS));
    }

    protected function readIdMappings(\stdClass $json): DeploymentMappings
    {
        return JsonUtil::asList(JsonUtil::getArray($json, self::PROCESS_INSTANCE_ID_MAPPINGS), DeploymentMappingJsonConverter::instance(), DeploymentMappings::class);
    }
}
