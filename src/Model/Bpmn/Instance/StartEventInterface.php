<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\StartEventBuilder;

interface StartEventInterface extends CatchEventInterface
{
    public function builder(): StartEventBuilder;

    public function isInterrupting(): bool;

    public function setInterrupting(bool $isInterrupting): void;

    public function getFormHandlerCLass(): string;

    public function setFormHandlerClass(string $formHandlerClass): void;

    public function getFormKey(): string;

    public function setFormKey(string $formKey): void;

    public function getInitiator(): string;

    public function setInitiator(string $initiator): void;
}
