<?php

namespace Jabe\Model\Xml\Impl;

use Jabe\Model\Xml\ModelInterface;
use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Exception\ModelException;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Util\ModelUtil;
use Jabe\Model\Xml\Impl\Validation\ModelInstanceValidator;
use Jabe\Model\Xml\Instance\{
    DomDocumentInterface,
    ModelElementInstanceInterface
};
use Jabe\Model\Xml\Type\ModelElementTypeInterface;
use Jabe\Model\Xml\Validation\ValidationResultsInterface;

class ModelInstanceImpl implements ModelInstanceInterface
{
    protected $document;
    protected $model;
    protected $modelBuilder;

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
    public function newInstance($type, ?string $id = null): ModelElementInstanceInterface
    {
        if (is_string($type)) {
            $modelElementType = $this->model->getType($type);
            if ($modelElementType != null) {
                $type = $modelElementType;
            } else {
                throw new ModelException(
                    sprintf("Cannot create instance of ModelType %s: no such type registered.", $type)
                );
            }
        }
        $modelElementInstance = $type->newInstance($this);
        if (!empty($id)) {
            ModelUtil::setNewIdentifier($type, $modelElementInstance, $id, false);
        } else {
            ModelUtil::setGeneratedUniqueIdentifier($type, $modelElementInstance, false);
        }
        return $modelElementInstance;
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function registerGenericType(string $namespaceUri, string $localName): ModelElementTypeInterface
    {
        $elementType = $this->model->getTypeForName($namespaceUri, $localName);
        if ($elementType == null) {
            $elementType = $this->modelBuilder->defineGenericType($localName, $namespaceUri);
            $model = $this->modelBuilder->build();
        }
        return $elementType;
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
                if (!($modelElementType->isAbstract())) {
                    $instances = array_merge($instances, $modelElementType->getInstances($this));
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
