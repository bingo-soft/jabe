<?php

namespace BpmPlatform\Model\Xml\Impl;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Exception\ModelException;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Impl\Validation\ModelInstanceValidator;
use BpmPlatform\Model\Xml\Instance\{
    DomDocumentInterface,
    ModelElementInstanceInterface
};
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;
use BpmPlatform\Model\Xml\Validation\ValidationResultsInterface;

class ModelInstanceImpl implements ModelInstanceInterface
{
    private $document;
    private $model;
    private $modelBuilder;

    public function __construct(ModelImpl $model, ModelBuilder $modelBuilder, DomDocumentInterface $document)
    {
        $this->document = $document;
        $this->model = $model;
        $this->modelBuilder = $modelBuilder;
    }

    public function getDocument(): DomDocumentInterface
    {
        return $this->document;
    }

    public function getDocumentElement(): ?ModelElementInstanceInterface
    {
        $rootElement = $this->document->getRootElement();
        if ($rootElement != null) {
            return ModelUtil::getModelElement($rootElement, $this);
        } else {
            return null;
        }
    }

    public function setDocumentElement(ModelElementInstanceInterface $modelElement): void
    {
        ModelUtil::ensureInstanceOf($modelElement, ModelElementInstanceImpl::class);
        $domElement = $modelElement->getDomElement();
        $this->document->setRootElement($domElement);
    }

    /**
     * @param mixed $type
     */
    public function newInstance($type, ?string $id): ModelElementInstanceInterface
    {
        $modelElementType = $this->model->getType($type);
        if ($modelElementType != null) {
            $modelElementInstance = $modelElementType->newInstance($this, null);
            if (!empty($id)) {
                ModelUtil::setNewIdentifier($type, $modelElementInstance, $id, false);
            } else {
                ModelUtil::setGeneratedUniqueIdentifier($type, $modelElementInstance, false);
            }
            return $modelElementInstance;
        } else {
            throw new ModelException(
                sprintf("Cannot create instance of ModelType %s: no such type registered.", $type)
            );
        }
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function getModelElementById(?string $id): ?ModelElementInstanceInterface
    {
        if ($id == null) {
            return null;
        }
        $element = $this->document->getElementById($id);
        if ($element != null) {
            return ModelUtil::getModelElement($element, $this);
        } else {
            return null;
        }
    }

    /**
     * @param mixed $reference
     */
    public function getModelElementsByType($reference): array
    {
        if ($reference instanceof ModelElementTypeInterface) {
            $extendingTypes = $reference->getAllExtendingTypes();
            $instances = [];
            foreach ($extendingTypes as $modelElementType) {
                if (!$modelElementType->isAbstract()) {
                    $instances[] = $modelElementType->getInstances($this);
                }
            }
            return $instances;
        } elseif (is_string($reference)) {
            return $this->getModelElementsByType($this->getModel()->getType($reference));
        }
        return [];
    }

    public function clone(): ModelInstanceInterface
    {
        return new ModelInstanceImpl($this->model, $this->modelBuilder, $this->document->clone());
    }

    public function validate(array $validators): ValidationResultsInterface
    {
        return (new ModelInstanceValidator($this, $validators))->validate();
    }
}
