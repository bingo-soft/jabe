<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Engine\Impl\Migration\Batch\MigrationBatchConfiguration;
use Jabe\Engine\Impl\Util\JsonUtil;

class MigrationBatchConfigurationJsonConverter extends JsonObjectConverter
{
    private static $INSTANCE;

    public const MIGRATION_PLAN = "migrationPlan";
    public const PROCESS_INSTANCE_IDS = "processInstanceIds";
    public const PROCESS_INSTANCE_ID_MAPPINGS = "processInstanceIdMappings";
    public const SKIP_LISTENERS = "skipListeners";
    public const SKIP_IO_MAPPINGS = "skipIoMappings";
    public const BATCH_ID = "batchId";

    public static function instance(): MigrationBatchConfigurationJsonConverter
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new MigrationBatchConfigurationJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*MigrationBatchConfiguration*/$configuration, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();

        JsonUtil::addField($json, self::MIGRATION_PLAN, MigrationPlanJsonConverter::instance(), $configuration->getMigrationPlan());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::PROCESS_INSTANCE_ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addField($json, self::SKIP_LISTENERS, $configuration->isSkipCustomListeners());
        JsonUtil::addField($json, self::SKIP_IO_MAPPINGS, $configuration->isSkipIoMappings());
        JsonUtil::addField($json, self::BATCH_ID, $configuration->getBatchId());

        return $json;
    }

    public function toObject(\stdClass $json, bool $isOrQuery = false)
    {
        return new MigrationBatchConfiguration(
            $this->readProcessInstanceIds($json),
            $this->readIdMappings($json),
            JsonUtil::asJavaObject(JsonUtil::getObject($json, self::MIGRATION_PLAN), MigrationPlanJsonConverter::instance()),
            JsonUtil::getBoolean($json, self::SKIP_LISTENERS),
            JsonUtil::getBoolean($json, self::SKIP_IO_MAPPINGS),
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
