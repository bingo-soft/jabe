<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface InteractionNodeInterface extends ModelElementInstanceInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;
}
