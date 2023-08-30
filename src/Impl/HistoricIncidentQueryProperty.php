<?php

namespace Jabe\Impl;

class HistoricIncidentQueryProperty
{
    private static $INCIDENT_ID;
    private static $INCIDENT_MESSAGE;
    private static $INCIDENT_CREATE_TIME;
    private static $INCIDENT_END_TIME;
    private static $INCIDENT_TYPE;
    private static $EXECUTION_ID;
    private static $ACTIVITY_ID;
    private static $PROCESS_INSTANCE_ID;
    private static $PROCESS_DEFINITION_ID;
    private static $PROCESS_DEFINITION_KEY;
    private static $CAUSE_INCIDENT_ID;
    private static $ROOT_CAUSE_INCIDENT_ID;
    private static $HISTORY_CONFIGURATION;
    private static $CONFIGURATION;
    private static $TENANT_ID;
    private static $INCIDENT_STATE;

    public function incidentId(): QueryPropertyImpl
    {
        if (self::$INCIDENT_ID === null) {
            self::$INCIDENT_ID = new QueryPropertyImpl("ID_");
        }
        return self::$INCIDENT_ID;
    }

    public function incidentMessage(): QueryPropertyImpl
    {
        if (self::$INCIDENT_MESSAGE === null) {
            self::$INCIDENT_MESSAGE = new QueryPropertyImpl("INCIDENT_MSG_");
        }
        return self::$INCIDENT_MESSAGE;
    }

    public static function incidentCreateTime(): QueryPropertyImpl
    {
        if (self::$INCIDENT_CREATE_TIME === null) {
            self::$INCIDENT_CREATE_TIME = new QueryPropertyImpl("CREATE_TIME_");
        }
        return self::$INCIDENT_CREATE_TIME;
    }

    public static function incidentEndTime(): QueryPropertyImpl
    {
        if (self::$INCIDENT_END_TIME === null) {
            self::$INCIDENT_END_TIME = new QueryPropertyImpl("END_TIME_");
        }
        return self::$INCIDENT_END_TIME;
    }

    public static function incidentType(): QueryPropertyImpl
    {
        if (self::$INCIDENT_TYPE === null) {
            self::$INCIDENT_TYPE = new QueryPropertyImpl("INCIDENT_TYPE_");
        }
        return self::$INCIDENT_TYPE;
    }

    public static function executionId(): QueryPropertyImpl
    {
        if (self::$EXECUTION_ID === null) {
            self::$EXECUTION_ID = new QueryPropertyImpl("EXECUTION_ID_");
        }
        return self::$EXECUTION_ID;
    }

    public static function activityId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_ID === null) {
            self::$ACTIVITY_ID = new QueryPropertyImpl("ACTIVITY_ID_");
        }
        return self::$ACTIVITY_ID;
    }

    public static function processInstanceId(): QueryPropertyImpl
    {
        if (self::$PROCESS_INSTANCE_ID === null) {
            self::$PROCESS_INSTANCE_ID = new QueryPropertyImpl("PROC_INST_ID_");
        }
        return self::$PROCESS_INSTANCE_ID;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID === null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("PROC_DEF_ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_KEY === null) {
            self::$PROCESS_DEFINITION_KEY = new QueryPropertyImpl("PROC_DEF_KEY_");
        }
        return self::$PROCESS_DEFINITION_KEY;
    }

    public static function causeIncidentId(): QueryPropertyImpl
    {
        if (self::$CAUSE_INCIDENT_ID === null) {
            self::$CAUSE_INCIDENT_ID = new QueryPropertyImpl("CAUSE_INCIDENT_ID_");
        }
        return self::$CAUSE_INCIDENT_ID;
    }

    public static function rootCauseIncidentId(): QueryPropertyImpl
    {
        if (self::$ROOT_CAUSE_INCIDENT_ID === null) {
            self::$ROOT_CAUSE_INCIDENT_ID = new QueryPropertyImpl("ROOT_CAUSE_INCIDENT_ID_");
        }
        return self::$ROOT_CAUSE_INCIDENT_ID;
    }

    public static function historyConfiguration(): QueryPropertyImpl
    {
        if (self::$HISTORY_CONFIGURATION === null) {
            self::$HISTORY_CONFIGURATION = new QueryPropertyImpl("HISTORY_CONFIGURATION_");
        }
        return self::$HISTORY_CONFIGURATION;
    }

    public static function configuration(): QueryPropertyImpl
    {
        if (self::$CONFIGURATION === null) {
            self::$CONFIGURATION = new QueryPropertyImpl("CONFIGURATION_");
        }
        return self::$CONFIGURATION;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID === null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }

    public static function incidentState(): QueryPropertyImpl
    {
        if (self::$INCIDENT_STATE === null) {
            self::$INCIDENT_STATE = new QueryPropertyImpl("INCIDENT_STATE_");
        }
        return self::$INCIDENT_STATE;
    }
}
