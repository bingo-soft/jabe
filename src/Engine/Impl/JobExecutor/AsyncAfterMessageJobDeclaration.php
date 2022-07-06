<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Pvm\Runtime\AtomicOperation;

class AsyncAfterMessageJobDeclaration extends MessageJobDeclaration
{
    public static $asyncAfterOperations;

    public function __construct()
    {
        if (self::$asyncAfterOperations === null) {
            self::$asyncAfterOperations = [
                AtomicOperation::transitionNotifyListenerTake()->getCanonicalName(),
                AtomicOperation::activityEnd()->getCanonicalName(),
            ];
        }
        parent::__construct(self::$asyncAfterOperations);
        $this->setJobConfiguration(self::ASYNC_AFTER);
    }
}
