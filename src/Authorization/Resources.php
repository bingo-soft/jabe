<?php

namespace Jabe\Authorization;

use Jabe\EntityTypes;

/**
 * The set of built-in Resource names.
 *
 */
class Resources implements ResourceInterface
{
    public static $APPLICATION;

    public static function application(): ResourceInterface
    {
        if (self::$APPLICATION === null) {
            self::$APPLICATION = new Resources(EntityTypes::APPLICATION, 0);
        }
        return self::$APPLICATION;
    }

    public static $USER;

    public static function user(): ResourceInterface
    {
        if (self::$USER === null) {
            self::$USER = new Resources(EntityTypes::USER, 1);
        }
        return self::$USER;
    }

    public static $GROUP;

    public static function group(): ResourceInterface
    {
        if (self::$GROUP === null) {
            self::$GROUP = new Resources(EntityTypes::GROUP, 2);
        }
        return self::$GROUP;
    }

    public static $GROUP_MEMBERSHIP;

    public static function groupMembership(): ResourceInterface
    {
        if (self::$GROUP_MEMBERSHIP === null) {
            self::$GROUP_MEMBERSHIP = new Resources(EntityTypes::GROUP_MEMBERSHIP, 3);
        }
        return self::$GROUP_MEMBERSHIP;
    }

    public static $AUTHORIZATION;

    public static function authorization(): ResourceInterface
    {
        if (self::$AUTHORIZATION === null) {
            self::$AUTHORIZATION = new Resources(EntityTypes::AUTHORIZATION, 4);
        }
        return self::$AUTHORIZATION;
    }

    public static $FILTER;

    public static function filter(): ResourceInterface
    {
        if (self::$FILTER === null) {
            self::$FILTER = new Resources(EntityTypes::FILTER, 5);
        }
        return self::$FILTER;
    }

    public static $PROCESS_DEFINITION;

    public static function processDefinition(): ResourceInterface
    {
        if (self::$PROCESS_DEFINITION === null) {
            self::$PROCESS_DEFINITION = new Resources(EntityTypes::PROCESS_DEFINITION, 6);
        }
        return self::$PROCESS_DEFINITION;
    }

    public static $TASK;

    public static function task(): ResourceInterface
    {
        if (self::$TASK === null) {
            self::$TASK = new Resources(EntityTypes::TASK, 7);
        }
        return self::$TASK;
    }

    public static $PROCESS_INSTANCE;

    public static function processInstance(): ResourceInterface
    {
        if (self::$PROCESS_INSTANCE === null) {
            self::$PROCESS_INSTANCE = new Resources(EntityTypes::PROCESS_INSTANCE, 8);
        }
        return self::$PROCESS_INSTANCE;
    }

    public static $DEPLOYMENT;

    public static function deployment(): ResourceInterface
    {
        if (self::$DEPLOYMENT === null) {
            self::$DEPLOYMENT = new Resources(EntityTypes::DEPLOYMENT, 9);
        }
        return self::$DEPLOYMENT;
    }

    public static $DECISION_DEFINITION;

    public static function decisionDefinition(): ResourceInterface
    {
        if (self::$DECISION_DEFINITION === null) {
            self::$DECISION_DEFINITION = new Resources(EntityTypes::DECISION_DEFINITION, 10);
        }
        return self::$DECISION_DEFINITION;
    }

    public static $TENANT;

    public static function tenant(): ResourceInterface
    {
        if (self::$TENANT === null) {
            self::$TENANT = new Resources(EntityTypes::TENANT, 11);
        }
        return self::$TENANT;
    }

    public static $TENANT_MEMBERSHIP;

    public static function tenantMembership(): ResourceInterface
    {
        if (self::$TENANT_MEMBERSHIP === null) {
            self::$TENANT_MEMBERSHIP = new Resources(EntityTypes::TENANT_MEMBERSHIP, 12);
        }
        return self::$TENANT_MEMBERSHIP;
    }

    public static $BATCH;

    public static function batch(): ResourceInterface
    {
        if (self::$BATCH === null) {
            self::$BATCH = new Resources(EntityTypes::BATCH, 13);
        }
        return self::$BATCH;
    }

    public static $DECISION_REQUIREMENTS_DEFINITION;

    public static function decisionRequirementsDefinition(): ResourceInterface
    {
        if (self::$DECISION_REQUIREMENTS_DEFINITION === null) {
            self::$DECISION_REQUIREMENTS_DEFINITION = new Resources(EntityTypes::DECISION_REQUIREMENTS_DEFINITION, 14);
        }
        return self::$DECISION_REQUIREMENTS_DEFINITION;
    }

    public static $REPORT;

    public static function report(): ResourceInterface
    {
        if (self::$REPORT === null) {
            self::$REPORT = new Resources(EntityTypes::REPORT, 15);
        }
        return self::$REPORT;
    }

    public static $DASHBOARD;

    public static function dashboard(): ResourceInterface
    {
        if (self::$DASHBOARD === null) {
            self::$DASHBOARD = new Resources(EntityTypes::DASHBOARD, 16);
        }
        return self::$DASHBOARD;
    }

    public static $OPERATION_LOG_CATEGORY;

    public static function operationLogCategory(): ResourceInterface
    {
        if (self::$OPERATION_LOG_CATEGORY === null) {
            self::$OPERATION_LOG_CATEGORY = new Resources(EntityTypes::OPERATION_LOG_CATEGORY, 17);
        }
        return self::$OPERATION_LOG_CATEGORY;
    }

    //@DEPRECATED
    public static $OPTIMIZE;

    public static function optimize(): ResourceInterface
    {
        if (self::$OPTIMIZE === null) {
            self::$OPTIMIZE = new Resources(EntityTypes::OPTIMIZE, 18);
        }
        return self::$OPTIMIZE;
    }

    public static $HISTORIC_TASK;

    public static function historicTask(): ResourceInterface
    {
        if (self::$HISTORIC_TASK === null) {
            self::$HISTORIC_TASK = new Resources(EntityTypes::HISTORIC_TASK, 19);
        }
        return self::$HISTORIC_TASK;
    }

    public static $HISTORIC_PROCESS_INSTANCE;

    public static function historicProcessInstance(): ResourceInterface
    {
        if (self::$HISTORIC_PROCESS_INSTANCE === null) {
            self::$HISTORIC_PROCESS_INSTANCE = new Resources(EntityTypes::HISTORIC_PROCESS_INSTANCE, 20);
        }
        return self::$HISTORIC_PROCESS_INSTANCE;
    }

    public static $SYSTEM;

    public static function system(): ResourceInterface
    {
        if (self::$SYSTEM === null) {
            self::$SYSTEM = new Resources(EntityTypes::SYSTEM, 21);
        }
        return self::$SYSTEM;
    }

    private $name;
    private $id;

    private function __construct(?string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function resourceName(): ?string
    {
        return $this->name;
    }

    public function resourceType(): int
    {
        return $this->id;
    }
}
