<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

interface InteractionNodeInterface extends ModelElementInstanceInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;
}
