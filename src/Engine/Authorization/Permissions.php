<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permission types.
 */
class Permissions implements PermissionInterface
{
    use PermissionTrait;

    public const NONE = [
        0,
        Resources::APPLICATION,
        Resources::USER,
        Resources::GROUP,
        Resources::GROUP_MEMBERSHIP,
        Resources::AUTHORIZATION,
        Resources::PROCESS_DEFINITION,
        Resources::TASK,
        Resources::PROCESS_INSTANCE,
        Resources::DEPLOYMENT,
        Resources::DECISION_DEFINITION,
        Resources::TENANT,
        Resources::TENANT_MEMBERSHIP,
        Resources::BATCH,
        Resources::DECISION_REQUIREMENTS_DEFINITION,
        Resources::REPORT,
        Resources::DASHBOARD,
        Resources::OPERATION_LOG_CATEGORY,
        Resources::OPTIMIZE,
        Resources::HISTORIC_TASK,
        Resources::HISTORIC_PROCESS_INSTANCE
    ];

    public const ALL = [
        PHP_INT_MAX,
        Resources::APPLICATION,
        Resources::USER,
        Resources::GROUP,
        Resources::GROUP_MEMBERSHIP,
        Resources::AUTHORIZATION,
        Resources::PROCESS_DEFINITION,
        Resources::TASK,
        Resources::PROCESS_INSTANCE,
        Resources::DEPLOYMENT,
        Resources::DECISION_DEFINITION,
        Resources::TENANT,
        Resources::TENANT_MEMBERSHIP,
        Resources::BATCH,
        Resources::DECISION_REQUIREMENTS_DEFINITION,
        Resources::REPORT,
        Resources::DASHBOARD,
        Resources::OPERATION_LOG_CATEGORY,
        Resources::OPTIMIZE,
        Resources::HISTORIC_TASK,
        Resources::HISTORIC_PROCESS_INSTANCE
    ];

    public const READ = [
        2,
        Resources::USER,
        Resources::GROUP,
        Resources::AUTHORIZATION,
        Resources::PROCESS_DEFINITION,
        Resources::TASK,
        Resources::PROCESS_INSTANCE,
        Resources::DEPLOYMENT,
        Resources::DECISION_DEFINITION,
        Resources::TENANT,
        Resources::BATCH,
        Resources::DECISION_REQUIREMENTS_DEFINITION,
        Resources::REPORT,
        Resources::DASHBOARD
    ];

    public const UPDATE = [
        4,
        Resources::USER,
        Resources::GROUP,
        Resources::AUTHORIZATION,
        Resources::PROCESS_DEFINITION,
        Resources::TASK,
        Resources::PROCESS_INSTANCE,
        Resources::DECISION_DEFINITION,
        Resources::TENANT,
        Resources::BATCH,
        Resources::REPORT,
        Resources::DASHBOARD
    ];

    public const CREATE = [
        8,
        Resources::USER,
        Resources::GROUP,
        Resources::GROUP_MEMBERSHIP,
        Resources::AUTHORIZATION,
        Resources::TASK,
        Resources::PROCESS_INSTANCE,
        Resources::DEPLOYMENT,
        Resources::TENANT,
        Resources::TENANT_MEMBERSHIP,
        Resources::BATCH,
        Resources::REPORT,
        Resources::DASHBOARD
    ];

    public const DELETE = [
        16,
        Resources::USER,
        Resources::GROUP,
        Resources::GROUP_MEMBERSHIP,
        Resources::AUTHORIZATION,
        Resources::PROCESS_DEFINITION,
        Resources::TASK,
        Resources::PROCESS_INSTANCE,
        Resources::DEPLOYMENT,
        Resources::TENANT,
        Resources::TENANT_MEMBERSHIP,
        Resources::BATCH,
        Resources::REPORT,
        Resources::DASHBOARD
    ];

    public const ACCESS = [
        32,
        Resources::APPLICATION
    ];

    public const READ_TASK = [
        64,
        Resources::PROCESS_DEFINITION
    ];

    public const UPDATE_TASK = [
        128,
        Resources::PROCESS_DEFINITION
    ];

    public const CREATE_INSTANCE = [
        128,
        Resources::DECISION_DEFINITION,
        Resources::PROCESS_DEFINITION
    ];

    public const READ_INSTANCE = [
        512,
        Resources::PROCESS_DEFINITION
    ];

    public const UPDATE_INSTANCE = [
        1024,
        Resources::PROCESS_DEFINITION
    ];

    public const DELETE_INSTANCE = [
        2048,
        Resources::PROCESS_DEFINITION
    ];

    public const READ_HISTORY = [
        4096,
        Resources::BATCH,
        Resources::DECISION_DEFINITION,
        Resources::PROCESS_DEFINITION,
        Resources::TASK
    ];

    public const DELETE_HISTORY = [
        8192,
        Resources::BATCH,
        Resources::DECISION_DEFINITION,
        Resources::PROCESS_DEFINITION
    ];

    public const TASK_WORK = [
        16384,
        Resources::PROCESS_DEFINITION,
        Resources::TASK
    ];

    public const TASK_ASSIGN = [
        32768,
        Resources::PROCESS_DEFINITION,
        Resources::TASK
    ];

    public const MIGRATE_INSTANCE = [
        65536,
        Resources::PROCESS_DEFINITION
    ];

    private $resourceTypes;

    public function __construct(string $name)
    {
        $ref = new \ReflectionClass(__CLASS__);
        $constants = $ref->getConstants();
        if (array_key_exists($name, $constants)) {
            $this->name = $name;
            $this->id = $constants[$name][0];
            $this->resourceTypes = array_slice($constants[$name], 1);
        } else {
            throw new PermissionNotFound(sprintf("Permission %s not found", $name));
        }
    }
}
