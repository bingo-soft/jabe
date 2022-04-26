<?php

namespace Jabe\Model\Xml\Type;

use Jabe\Model\Xml\ModelInterface;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Instance\{
    DomElementInterface,
    ModelElementInstanceInterface
};
use Jabe\Model\Xml\Type\Attribute\AttributeInterface;

interface ModelElementTypeInterface
{
    public function getTypeName(): string;

    public function getTypeNamespace(): ?string;

    public function getInstanceType(): string;

    public function getAttributes(): array;

    public function newInstance(
        ModelInstanceInterface $instance,
        ?DomElementInterface $domElement = null
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
