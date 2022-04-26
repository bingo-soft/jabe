<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Pvm\Runtime\AtomicOperation;

class AsyncBeforeMessageJobDeclaration extends MessageJobDeclaration
{
    public static $asyncBeforeOperations;

    public function __construct()
    {
        if (self::$asyncBeforeOperations == null) {
            self::$asyncBeforeOperations = [
                AtomicOperation::transitionCreateScope()->getCanonicalName(),
                AtomicOperation::processStart()->getCanonicalName(),
                AtomicOperation::activityStartCreateScope()->getCanonicalName(),
            ];
        }
        parent::__construct(self::$asyncBeforeOperations);
        $this->setJobConfiguration(self::ASYNC_BEFORE);
    }
}
