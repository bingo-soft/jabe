<?php

namespace Jabe\Impl\Json;

use Jabe\Impl\Migration\MigrationPlanImpl;
use Jabe\Impl\Util\JsonUtil;

class MigrationPlanJsonConverter extends JsonObjectConverter
{
    private static $INSTANCE;
    public const SOURCE_PROCESS_DEFINITION_ID = "sourceProcessDefinitionId";
    public const TARGET_PROCESS_DEFINITION_ID = "targetProcessDefinitionId";
    public const INSTRUCTIONS = "instructions";

    public static function instance(): MigrationPlanJsonConverter
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new MigrationPlanJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*MigrationPlanInterface*/$migrationPlan, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();

        JsonUtil::addField($json, self::SOURCE_PROCESS_DEFINITION_ID, $migrationPlan->getSourceProcessDefinitionId());
        JsonUtil::addField($json, self::TARGET_PROCESS_DEFINITION_ID, $migrationPlan->getTargetProcessDefinitionId());
        JsonUtil::addListField($json, self::INSTRUCTIONS, MigrationInstructionJsonConverter::instance(), $migrationPlan->getInstructions());

        return $json;
    }

    public function toObject(\stdClass $json, bool $isOrQuery = false)
    {
        $migrationPlan = new MigrationPlanImpl(
            JsonUtil::getString($json, self::SOURCE_PROCESS_DEFINITION_ID),
            JsonUtil::getString($json, self::TARGET_PROCESS_DEFINITION_ID)
        );
        $migrationPlan->setInstructions(
            JsonUtil::asList(JsonUtil::getArray($json, self::INSTRUCTIONS), MigrationInstructionJsonConverter::instance())
        );
        return $migrationPlan;
    }
}
