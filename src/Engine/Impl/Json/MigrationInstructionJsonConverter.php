<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\Migration\MigrationInstructionImpl;
use Jabe\Engine\Impl\Util\JsonUtil;
use Jabe\Engine\Migration\MigrationInstructionInterface;

class MigrationInstructionJsonConverter extends JsonObjectConverter
{
    private static $INSTANCE;// = new MigrationInstructionJsonConverter();

    public const SOURCE_ACTIVITY_IDS = "sourceActivityIds";
    public const TARGET_ACTIVITY_IDS = "targetActivityIds";
    public const UPDATE_EVENT_TRIGGER = "updateEventTrigger";

    public function toJsonObject(/*MigrationInstructionInterface*/$instruction, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();

        JsonUtil::addArrayField($json, self::SOURCE_ACTIVITY_IDS, [$instruction->getSourceActivityId()]);
        JsonUtil::addArrayField($json, self::TARGET_ACTIVITY_IDS, [$instruction->getTargetActivityId()]);
        JsonUtil::addField($json, self::UPDATE_EVENT_TRIGGER, $instruction->isUpdateEventTrigger());

        return $json;
    }

    public function toObject(\stdClass $json, bool $isOrQuery = false)
    {
        return new MigrationInstructionImpl(
            $this->readSourceActivityId($json),
            $this->readTargetActivityId($json),
            JsonUtil::getBoolean($json, self::UPDATE_EVENT_TRIGGER)
        );
    }

    protected function readSourceActivityId($son): string
    {
        return JsonUtil::getString(JsonUtil::getArray($json, self::SOURCE_ACTIVITY_IDS));
    }

    protected function readTargetActivityId($json): string
    {
        return JsonUtil::getString(JsonUtil::getArray($json, self::TARGET_ACTIVITY_IDS));
    }
}
