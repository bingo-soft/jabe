<?php

namespace BpmPlatform\Engine\Impl\Core\Handler;

use BpmPlatform\Engine\Impl\Core\Model\CoreActivity;

interface HandlerContextInterface
{
    public function getParent(): CoreActivity;
}
