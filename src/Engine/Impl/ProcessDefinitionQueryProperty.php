<?php

namespace Jabe\Engine\Impl;

class ProcessDefinitionQueryProperty
{
    private static $PROCESS_DEFINITION_KEY;// new QueryPropertyImpl("KEY_");
    private static $PROCESS_DEFINITION_CATEGORY;// new QueryPropertyImpl("CATEGORY_");
    private static $PROCESS_DEFINITION_ID;// new QueryPropertyImpl("ID_");
    private static $PROCESS_DEFINITION_VERSION;// new QueryPropertyImpl("VERSION_");
    private static $PROCESS_DEFINITION_NAME;// new QueryPropertyImpl("NAME_");
    private static $DEPLOYMENT_ID;// new QueryPropertyImpl("DEPLOYMENT_ID_");
    private static $DEPLOY_TIME;// new QueryPropertyImpl("DEPLOY_TIME_");
    private static $TENANT_ID;// new QueryPropertyImpl("TENANT_ID_");
    private static $VERSION_TAG;// new QueryPropertyImpl("VERSION_TAG_");

    public static function processDefinitionKey(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_KEY == null) {
            self::$PROCESS_DEFINITION_KEY = new QueryPropertyImpl("KEY_");
        }
        return self::$PROCESS_DEFINITION_KEY;
    }

    public static function processDefinitionCategory(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_CATEGORY == null) {
            self::$PROCESS_DEFINITION_CATEGORY = new QueryPropertyImpl("CATEGORY_");
        }
        return self::$PROCESS_DEFINITION_CATEGORY;
    }

    public static function processDefinitionId(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_ID == null) {
            self::$PROCESS_DEFINITION_ID = new QueryPropertyImpl("ID_");
        }
        return self::$PROCESS_DEFINITION_ID;
    }

    public static function processDefinitionVersion(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_VERSION == null) {
            self::$PROCESS_DEFINITION_VERSION = new QueryPropertyImpl("VERSION_");
        }
        return self::$PROCESS_DEFINITION_VERSION;
    }

    public static function processDefinitionName(): QueryPropertyImpl
    {
        if (self::$PROCESS_DEFINITION_NAME == null) {
            self::$PROCESS_DEFINITION_NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$PROCESS_DEFINITION_NAME;
    }

    public static function deploymentId(): QueryPropertyImpl
    {
        if (self::$DEPLOYMENT_ID == null) {
            self::$DEPLOYMENT_ID = new QueryPropertyImpl("DEPLOYMENT_ID_");
        }
        return self::$DEPLOYMENT_ID;
    }

    public static function deployTime(): QueryPropertyImpl
    {
        if (self::$DEPLOY_TIME == null) {
            self::$DEPLOY_TIME = new QueryPropertyImpl("DEPLOY_TIME_");
        }
        return self::$DEPLOY_TIME;
    }

    public static function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID == null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }

    public static function versionTag(): QueryPropertyImpl
    {
        if (self::$VERSION_TAG == null) {
            self::$VERSION_TAG = new QueryPropertyImpl("VERSION_TAG_");
        }
        return self::$VERSION_TAG;
    }
}
