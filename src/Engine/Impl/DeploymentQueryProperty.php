<?php

namespace Jabe\Engine\Impl;

class DeploymentQueryProperty
{
    private static $DEPLOYMENT_ID;
    private static $DEPLOYMENT_NAME;
    private static $DEPLOY_TIME;
    private static $TENANT_ID;

    public function deploymentId(): QueryPropertyImpl
    {
        if (self::$DEPLOYMENT_ID == null) {
            self::$DEPLOYMENT_ID = new QueryPropertyImpl("ID_");
        }
        return self::$DEPLOYMENT_ID;
    }

    public function deploymentName(): QueryPropertyImpl
    {
        if (self::$DEPLOYMENT_NAME == null) {
            self::$DEPLOYMENT_NAME = new QueryPropertyImpl("NAME_");
        }
        return self::$DEPLOYMENT_NAME;
    }

    public function deployTime(): QueryPropertyImpl
    {
        if (self::$DEPLOY_TIME == null) {
            self::$DEPLOY_TIME = new QueryPropertyImpl("DEPLOY_TIME_");
        }
        return self::$DEPLOY_TIME;
    }

    public function tenantId(): QueryPropertyImpl
    {
        if (self::$TENANT_ID == null) {
            self::$TENANT_ID = new QueryPropertyImpl("TENANT_ID_");
        }
        return self::$TENANT_ID;
    }
}
