<?php

namespace BpmPlatform\Model\Xml\Type;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Instance\{
    DomElementInterface,
    ModelElementInstanceInterface
};
use BpmPlatform\Model\Xml\Type\Attribute\AttributeInterface;

interface ModelElementTypeInterface
{
    public function getTypeName(): string;

    public function getTypeNamespace(): string;

    public function getInstanceType(): string;

    public function getAttributes(): array;

    public function newInstance(
        ModelInstanceInterface $instance,
        ?DomElementInterface $domElement
    ): ModelElementInstanceInterface;

    public function getBaseType(): ?ModelElementTypeInterface;

    public function isAbstract(): bool;

    public function getExtendingTypes(): array;

    public function getAttribute(string $attribute): ?AttributeInterface;

    public function getModel(): ModelInterface;

    public function getInstances(ModelInstanceInterface $modelInstanceImpl): array;

    public function getChildElementTypes(): array;

    public function getAllChildElementTypes(): array;
}
