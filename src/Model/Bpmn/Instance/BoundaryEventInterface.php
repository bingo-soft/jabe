<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\BoundaryEventBuilder;

interface BoundaryEventInterface extends CatchEventInterface
{
    public function cancelActivity(): bool;

    public function setCancelActivity(bool $cancelActivity): void;

    public function getAttachedTo(): ActivityInterface;

    public function setAttachedTo(ActivityInterface $attachedTo): void;

    public function builder(): BoundaryEventBuilder;
}
