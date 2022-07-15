<?php

namespace Jabe\Engine\Impl\Batch\RemovalTime;

use Jabe\Engine\Impl\Batch\{
    DeploymentMappingJsonConverter,
    DeploymentMappings
};
use Jabe\Engine\Impl\Json\JsonObjectConverter;
use Jabe\Engine\Impl\Util\JsonUtil;

class SetRemovalTimeJsonConverter extends JsonObjectConverter
{
    public static $INSTANCE;

    protected const IDS = "ids";
    protected const ID_MAPPINGS = "idMappings";
    protected const REMOVAL_TIME = "removalTime";
    protected const HAS_REMOVAL_TIME = "hasRemovalTime";
    protected const IS_HIERARCHICAL = "isHierarchical";

    public function instance(): JsonObjectConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new SetRemovalTimeJsonConverter();
        }
        return self::$INSTANCE;
    }

    public function toJsonObject(/*SetRemovalTimeBatchConfiguration*/$configuration, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();

        JsonUtil::addListField($json, self::IDS, $configuration->getIds());
        JsonUtil::addListField($json, self::ID_MAPPINGS, DeploymentMappingJsonConverter::instance(), $configuration->getIdMappings());
        JsonUtil::addDateField($json, self::REMOVAL_TIME, $configuration->getRemovalTime());
        JsonUtil::addField($json, self::HAS_REMOVAL_TIME, $configuration->hasRemovalTime());
        JsonUtil::addField($json, self::IS_HIERARCHICAL, $configuration->isHierarchical());
        return $json;
    }

    public function toObject(\stdClass $jsonObject, bool $isOrQuery = false)
    {
        $removalTimeMills = JsonUtil::getLong($jsonObject, self::REMOVAL_TIME);
        $removalTime = $removalTimeMills > 0 ? (new \DateTime())->setTimestamp($removalTimeMills / 1000)->format('c') : null;

        $instanceIds =  JsonUtil::asStringList(JsonUtil::getArray($jsonObject, self::IDS));

        $mappings = JsonUtil::asList(
            JsonUtil::getArray($jsonObject, self::ID_MAPPINGS),
            DeploymentMappingJsonConverter::instance(),
            DeploymentMappings::class
        );

        $hasRemovalTime = JsonUtil::getBoolean($jsonObject, self::HAS_REMOVAL_TIME);

        $isHierarchical = JsonUtil::getBoolean($jsonObject, self::IS_HIERARCHICAL);

        return (new SetRemovalTimeBatchConfiguration($instanceIds, $mappings))
            ->setRemovalTime($removalTime)
            ->setHasRemovalTime($hasRemovalTime)
            ->setHierarchical($isHierarchical);
    }
}
