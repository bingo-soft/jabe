<?php

namespace Jabe\Model\Bpmn\Instance;

interface CallableElementInterface extends RootElementInterface
{
    public function getName(): ?string;

    public function setName(string $name): void;

    public function getSupportedInterfaces(): array;

    public function getIoSpecification(): IoSpecificationInterface;

    public function setIoSpecification(IoSpecificationInterface $ioSpecification): void;

    public function getIoBindings(): array;
}
