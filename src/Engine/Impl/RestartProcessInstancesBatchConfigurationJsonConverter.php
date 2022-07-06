<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Engine\Impl\Cmd\AbstractProcessInstanceModificationCommand;
use Jabe\Engine\Impl\Json\{
    JsonObjectConverter,
    ModificationCmdJsonConverter
};
use Jabe\Engine\Impl\Util\JsonUtil;

class RestartProcessInstancesBatchConfigurationJsonConverter extends JsonObjectConverter
{
    private static $INSTANCE;
    public const PROCESS_INSTANCE_IDS = "processInstanceIds";
    public const PROCESS_INSTANCE_ID_MAPPINGS = "processInstanceIdMappings";
    public const INSTRUCTIONS = "instructions";
    public const PROCESS_DEFINITION_ID = "processDefinitionId";
    public const INITIAL_VARIABLES = "initialVariables";
    public const SKIP_CUSTOM_LISTENERS = "skipCustomListeners";
    public const SKIP_IO_MAPPINGS = "skipIoMappings";
    public const WITHOUT_BUSINESS_KEY = "withoutBusinessKey";

    public static function instance(): RestartProcessInstancesBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new RestartProcessInstancesBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(RestartProcessInstancesBatchConfiguration $configuration): \stdClass
    {
        $json = JsonUtil::createObject();
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addField($json, self::PROCESS_DEFINITION_ID, $configuration->getProcessDefinitionId());
        JsonUtil::addListField($json, self::INSTRUCTIONS, ModificationCmdJsonConverter::instance(), $configuration->getInstructions());
        JsonUtil::addField($json, self::INITIAL_VARIABLES, $configuration->isInitialVariables());
        JsonUtil::addField($json, self::SKIP_CUSTOM_LISTENERS, $configuration->isSkipCustomListeners());
        JsonUtil::addField($json, self::SKIP_IO_MAPPINGS, $configuration->isSkipIoMappings());
        JsonUtil::addField($json, self::WITHOUT_BUSINESS_KEY, $configuration->isWithoutBusinessKey());
        return $json;
    }

    public function toObject(\stdClass $json): RestartProcessInstancesBatchConfiguration
    {
        $processInstanceIds = $this->readProcessInstanceIds($json);
        $idMappings = $this->readIdMappings($json);
        $instructions = JsonUtil::asList(JsonUtil::getArray($json, self::INSTRUCTIONS), ModificationCmdJsonConverter::instance());

        return new RestartProcessInstancesBatchConfiguration(
            $processInstanceIds,
            $idMappings,
            $instructions,
            JsonUtil::getString($json, self::PROCESS_DEFINITION_ID),
            JsonUtil::getBoolean($json, self::INITIAL_VARIABLES),
            JsonUtil::getBoolean($json, self::SKIP_CUSTOM_LISTENERS),
            JsonUtil::getBoolean($json, self::SKIP_IO_MAPPINGS),
            JsonUtil::getBoolean($json, self::WITHOUT_BUSINESS_KEY)
        );
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
