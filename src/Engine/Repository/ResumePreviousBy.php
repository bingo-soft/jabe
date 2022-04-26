<?php

namespace Jabe\Engine\Repository;

class ResumePreviousBy
{
    /**
     * Resume previous deployments that contain processes with the same key as in the new deployment
     */
    public const RESUME_BY_PROCESS_DEFINITION_KEY = "process-definition-key";

    /**
     * Resume previous deployments that have the same name as the new deployment
     */
    public const RESUME_BY_DEPLOYMENT_NAME = "deployment-name";
}
