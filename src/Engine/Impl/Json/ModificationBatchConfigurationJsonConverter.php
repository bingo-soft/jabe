<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\ModificationBatchConfiguration;
use Jabe\Engine\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Engine\Impl\Util\JsonUtil;

class ModificationBatchConfigurationJsonConverter extends JsonObjectConverter
{
    private static $INSTANCE;
    public const INSTRUCTIONS = "instructions";
    public const PROCESS_INSTANCE_IDS = "processInstanceIds";
    public const PROCESS_INSTANCE_ID_MAPPINGS = "processInstanceIdMappings";
    public const SKIP_LISTENERS = "skipListeners";
    public const SKIP_IO_MAPPINGS = "skipIoMappings";
    public const PROCESS_DEFINITION_ID = "processDefinitionId";

    public static function instance(): ModificationBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new ModificationBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*ModificationBatchConfiguration*/$configuration, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();
        JsonUtil::addListField($json, self::INSTRUCTIONS, ModificationCmdJsonConverter::instance(), $configuration->getInstructions());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addField($json, self::PROCESS_DEFINITION_ID, $configuration->getProcessDefinitionId());
        JsonUtil::addField($json, self::SKIP_LISTENERS, $configuration->isSkipCustomListeners());
        JsonUtil::addField($json, self::SKIP_IO_MAPPINGS, $configuration->isSkipIoMappings());

        return $json;
    }

    public function toObject(\stdClass $json, bool $isOrQuery = false)
    {
        $processInstanceIds = $this->readProcessInstanceIds($json);
        $mappings = $this->readIdMappings($json);
        $processDefinitionId = JsonUtil::getString($json, self::PROCESS_DEFINITION_ID);
        $instructions = JsonUtil::asList(
            JsonUtil::getArray($json, self::INSTRUCTIONS),
            ModificationCmdJsonConverter::instance()
        );
        $skipCustomListeners = JsonUtil::getBoolean($json, self::SKIP_LISTENERS);
        $skipIoMappings = JsonUtil::getBoolean($json, self::SKIP_IO_MAPPINGS);

        return new ModificationBatchConfiguration(
            $processInstanceIds,
            $mappings,
            $processDefinitionId,
            $instructions,
            $skipCustomListeners,
            $skipIoMappings
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
