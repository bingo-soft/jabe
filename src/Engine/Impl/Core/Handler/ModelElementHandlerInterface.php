<?php

namespace BpmPlatform\Engine\Impl\Core\Handler;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ModelElementHandlerInterface
{
    public function handleElement(ModelElementInstanceInterface $element, HandlerContextInterface $context);
}
