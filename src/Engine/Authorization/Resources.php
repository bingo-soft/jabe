<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\EntityTypes;
use BpmPlatform\Engine\Authorization\Exception\ResourceNotFound;

/**
 * The set of built-in Resource names.
 *
 */
class Resources implements ResourceInterface
{
    public const APPLICATION = EntityTypes::APPLICATION; //0
    public const USER = EntityTypes::USER; //1
    public const GROUP = EntityTypes::GROUP; //2
    public const GROUP_MEMBERSHIP = EntityTypes::GROUP_MEMBERSHIP; //3
    public const AUTHORIZATION = EntityTypes::AUTHORIZATION; //4
    public const FILTER = EntityTypes::AUTHORIZATION; //5
    public const PROCESS_DEFINITION = EntityTypes::PROCESS_DEFINITION; //6
    public const TASK = EntityTypes::TASK; //7
    public const PROCESS_INSTANCE = EntityTypes::PROCESS_INSTANCE; //8
    public const DEPLOYMENT = EntityTypes::DEPLOYMENT; //9
    public const DECISION_DEFINITION = EntityTypes::DECISION_DEFINITION; //10
    public const TENANT = EntityTypes::TENANT; //11
    public const TENANT_MEMBERSHIP = EntityTypes::TENANT_MEMBERSHIP; //12
    public const BATCH = EntityTypes::BATCH; //13
    public const DECISION_REQUIREMENTS_DEFINITION = EntityTypes::DECISION_REQUIREMENTS_DEFINITION; //14
    public const REPORT = EntityTypes::REPORT; //15
    public const DASHBOARD = EntityTypes::DASHBOARD; //16
    public const OPERATION_LOG_CATEGORY = EntityTypes::OPERATION_LOG_CATEGORY; //17
    //@DEPRECATED
    public const OPTIMIZE = EntityTypes::OPTIMIZE; //18
    public const HISTORIC_TASK = EntityTypes::HISTORIC_TASK; //19
    public const HISTORIC_PROCESS_INSTANCE = EntityTypes::HISTORIC_PROCESS_INSTANCE; //20

    private $name;
    private $id;

    public function __construct(string $name)
    {
        $ref = new \ReflectionClass(__CLASS__);
        $constants = $ref->getConstants();
        if (array_search($name, $constants) !== false) {
            $this->name = $name;
            $i = 0;
            foreach ($constants as $key => $value) {
                if ($value == $name) {
                    $this->id = $i;
                    break;
                }
                $i += 1;
            }
        } else {
            throw new ResourceNotFound(sprintf("Resource %s not found", $name));
        }
    }

    public function resourceName(): string
    {
        return $this->name;
    }

    public function resourceType(): int
    {
        return $this->id;
    }
}
