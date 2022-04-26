<?php

namespace Jabe\Engine\Impl\Core\Handler;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

interface ModelElementHandlerInterface
{
    public function handleElement(ModelElementInstanceInterface $element, HandlerContextInterface $context);
}
