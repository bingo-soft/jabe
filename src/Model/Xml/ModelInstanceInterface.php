<?php

namespace Jabe\Model\Xml;

use Jabe\Model\Xml\Instance\{
    DomDocumentInterface,
    ModelElementInstanceInterface
};
use Jabe\Model\Xml\Type\ModelElementTypeInterface;
use Jabe\Model\Xml\Validation\ValidationResultsInterface;

interface ModelInstanceInterface
{
    public function getDocument(): DomDocumentInterface;

    public function getDocumentElement(): ?ModelElementInstanceInterface;

    public function setDocumentElement(ModelElementInstanceInterface $documentElement): void;

    /**
     * @param mixed $type
     */
    public function newInstance($type, ?string $id): ModelElementInstanceInterface;

    public function getModel(): ModelInterface;

    public function getModelElementById(?string $id): ?ModelElementInstanceInterface;

    /**
     * @param mixed $reference
     */
    public function getModelElementsByType($reference): array;

    public function clone(): ModelInstanceInterface;

    public function validate(array $validators): ValidationResultsInterface;
}
