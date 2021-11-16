<?php

namespace BpmPlatform\Engine\Impl\Pvm;

interface ReadOnlyProcessDefinitionInterface extends PvmScopeInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getInitial(): PvmActivityInterface;

    public function getDiagramResourceName(): string;
}
