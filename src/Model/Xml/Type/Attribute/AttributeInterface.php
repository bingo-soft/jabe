<?php

namespace Jabe\Model\Xml\Type\Attribute;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelElementTypeInterface;

interface AttributeInterface
{
    /**
     * @return mixed
     */
    public function getValue(ModelElementInstanceInterface $modelElement);

    /**
     * @param ModelElementInstanceInterface $modelElement
     * @param mixed $value
     * @param bool $withReferenceUpdate
     */
    public function setValue(
        ModelElementInstanceInterface $modelElement,
        $value,
        bool $withReferenceUpdate = true
    ): void;

    /**
     * @return mixed
     */
    public function getDefaultValue();

    public function isRequired(): bool;

    public function getNamespaceUri(): ?string;

    public function getAttributeName(): string;

    public function isIdAttribute(): bool;

    public function getOwningElementType(): ModelElementTypeInterface;

    public function getIncomingReferences(): array;

    public function getOutgoingReferences(): array;
}
