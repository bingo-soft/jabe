<?php

namespace Jabe\Impl\Core\Handler;

use Jabe\Impl\Core\Model\CoreActivity;

interface HandlerContextInterface
{
    public function getParent(): CoreActivity;
}
