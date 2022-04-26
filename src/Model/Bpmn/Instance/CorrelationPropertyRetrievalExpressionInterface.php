<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Impl\Instance\MessagePath;

interface CorrelationPropertyRetrievalExpressionInterface extends BaseElementInterface
{
    public function getMessage(): MessageInterface;

    public function setMessage(MessageInterface $message): void;

    public function getMessagePath(): MessagePath;

    public function setMessagePath(MessagePath $messagePath): void;
}
