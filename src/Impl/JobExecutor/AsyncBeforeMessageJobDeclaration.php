<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Pvm\Runtime\AtomicOperation;

class AsyncBeforeMessageJobDeclaration extends MessageJobDeclaration
{
    public static $asyncBeforeOperations;

    public function __construct()
    {
        if (self::$asyncBeforeOperations === null) {
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
