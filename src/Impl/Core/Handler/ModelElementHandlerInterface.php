<?php

namespace Jabe\Impl\Core\Handler;

use Xml\Instance\ModelElementInstanceInterface;

interface ModelElementHandlerInterface
{
    public function handleElement(ModelElementInstanceInterface $element, HandlerContextInterface $context);
}
