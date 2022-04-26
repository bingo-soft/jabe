<?php

namespace Jabe\Engine\Impl\Core\Handler;

use Jabe\Engine\Impl\Core\Model\CoreActivity;

interface HandlerContextInterface
{
    public function getParent(): CoreActivity;
}
