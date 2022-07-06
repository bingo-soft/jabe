<?php

namespace Jabe\Model\Xml\Impl\Type;

use Jabe\Model\Xml\{
    ModelInterface,
    ModelInstanceInterface
};
use Jabe\Model\Xml\Exception\{
    ModelException,
    ModelTypeException
};
use Jabe\Model\Xml\Type\Child\ChildElementCollectionInterface;
use Jabe\Model\Xml\Impl\ModelImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Impl\Util\ModelUtil;
use Jabe\Model\Xml\Instance\{
    DomDocumentInterface,
    DomElementInterface,
    ModelElementInstanceInterface
};
use Jabe\Model\Xml\Type\{
    ModelElementTypeInterface,
    ModelTypeInstanceProviderInterface
};
use Jabe\Model\Xml\Type\Attribute\AttributeInterface;

class ModelElementTypeImpl implements ModelElementTypeInterface
{
    private $model;
    private $typeName;
    private $instanceType;
    private $typeNamespace;
    private $baseType;
    private $extendingTypes = [];
    private $attributes = [];
    private $childElementTypes = [];
    private $childElementCollections = [];
    private $instanceProvider;
    private $isAbstract;

    public function __construct(ModelImpl $model, string $name, string $instanceType)
    {
        $this->model = $model;
        $this->typeName = $name;
        $this->instanceType = $instanceType;
    }

    public function newInstance(
        ModelInstanceInterface $modelInstance,
        ?DomElementInterface $domElement = null
    ): ModelElementInstanceInterface {
        if ($domElement !== null) {
            $modelTypeInstanceContext = new ModelTypeInstanceContext($domElement, $modelInstance, $this);
            return $this->createModelElementInstance($modelTypeInstanceContext);
        } else {
            $document = $modelInstance->getDocument();
            $domElement = $document->createElement($this->typeNamespace, $this->typeName);
            return $this->newInstance($modelInstance, $domElement);
        }
    }

    public function registerAttribute(AttributeInterface $attribute): void
    {
        if (!in_array($attribute, $this->attributes)) {
            $this->attributes[] = $attribute;
        }
    }

    public function registerChildElementType(ModelElementTypeInterface $childElementType): void
    {
        if (!in_array($childElementType, $this->childElementTypes)) {
            $this->childElementTypes[] = $childElementType;
        }
    }

    public function registerChildElementCollection(ChildElementCollectionInterface $childElementCollection): void
    {
        if (!in_array($childElementCollection, $this->childElementCollections)) {
            $this->childElementCollections[] = $childElementCollection;
        }
    }

    public function registerExtendingType(ModelElementTypeInterface $modelType): void
    {
        if (!in_array($modelType, $this->extendingTypes)) {
            $this->extendingTypes[] = $modelType;
        }
    }

    protected function createModelElementInstance(
        ModelTypeInstanceContext $instanceContext
    ): ModelElementInstanceInterface {
        if ($this->isAbstract) {
            throw new ModelTypeException(
                sprintf(
                    "Model element type %s is abstract and no instances can be created.",
                    $this->getTypeName()
                )
            );
        } else {
            return $this->instanceProvider->newInstance($instanceContext);
        }
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function getInstanceType(): string
    {
        return $this->instanceType;
    }

    public function setTypeNamespace(?string $typeNamespace): void
    {
        $this->typeNamespace = $typeNamespace;
    }

    public function getTypeNamespace(): ?string
    {
        return $this->typeNamespace;
    }

    public function setBaseType(ModelElementTypeImpl $baseType): void
    {
        if ($this->baseType === null) {
            $this->baseType = $baseType;
        } elseif ($this->baseType != $baseType) {
            throw new ModelException(
                sprintf(
                    "Type can not have multiple base types. %s already extends type %s and can not also extend type %s",
                    get_class($this),
                    get_class($this->baseType),
                    get_class($this)
                )
            );
        }
    }

    public function setInstanceProvider(ModelTypeInstanceProviderInterface $instanceProvider): void
    {
        $this->instanceProvider = $instanceProvider;
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract ?? false;
    }

    public function setAbstract(bool $isAbstract): void
    {
        $this->isAbstract = $isAbstract;
    }

    public function getExtendingTypes(): array
    {
        return $this->extendingTypes;
    }

    public function getAllExtendingTypes(): array
    {
        $extendingTypes = [];
        $extendingTypes[] = $this;
        $this->resolveExtendingTypes($extendingTypes);
        return $extendingTypes;
    }

    public function resolveExtendingTypes(array &$allExtendingTypes): void
    {
        foreach ($this->extendingTypes as $modelElementTypeImpl) {
            if (!in_array($modelElementTypeImpl, $allExtendingTypes)) {
                $allExtendingTypes[] = $modelElementTypeImpl;
                $modelElementTypeImpl->resolveExtendingTypes($allExtendingTypes);
            }
        }
    }

    public function resolveBaseTypes(array &$baseTypes): void
    {
        if ($this->baseType !== null) {
            $baseTypes[] = $this->baseType;
            $this->baseType->resolveBaseTypes($baseTypes);
        }
    }

    public function getBaseType(): ?ModelElementTypeInterface
    {
        return $this->baseType;
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function getChildElementTypes(): array
    {
        return $this->childElementTypes;
    }

    public function getAllChildElementTypes(): array
    {
        $allChildElementTypes = [];
        if ($this->baseType !== null) {
            $allChildElementTypes = array_merge($allChildElementTypes, $this->baseType->getAllChildElementTypes());
        }
        $allChildElementTypes = array_merge($allChildElementTypes, $this->childElementTypes);
        return $allChildElementTypes;
    }

    public function getChildElementCollections(): array
    {
        return $this->childElementCollections;
    }

    public function getAllChildElementCollections(): array
    {
        $allChildElementCollections = [];
        if ($this->baseType !== null) {
            $allChildElementCollections = array_merge(
                $allChildElementCollections,
                $this->baseType->getAllChildElementCollections()
            );
        }
        $allChildElementCollections = array_merge($allChildElementCollections, $this->childElementCollections);
        return $allChildElementCollections;
    }

    public function getInstances(ModelInstanceInterface $modelInstanceImpl): array
    {
        $document = $modelInstanceImpl->getDocument();

        $elements = $this->getElementsByNameNs($document, $this->typeNamespace);

        $resultList = [];
        foreach ($elements as $element) {
            $resultList[] = ModelUtil::getModelElement($element, $modelInstanceImpl, $this);
        }
        return $resultList;
    }

    protected function getElementsByNameNs(DomDocumentInterface $document, string $namespaceURI): array
    {
        $elements = $document->getElementsByNameNs($namespaceURI, $this->typeName);

        if (empty($elements)) {
            $alternativeNamespaces = $this->getModel()->getAlternativeNamespaces($namespaceURI);

            if (!empty($alternativeNamespaces)) {
                foreach ($alternativeNamespaces as $namespace) {
                    $elements = $this->getElementsByNameNs($document, $namespace);
                    if (!empty($elements)) {
                        break;
                    }
                }
            }
        }

        return $elements;
    }

    public function isBaseTypeOf(ModelElementTypeInterface $elementType): bool
    {
        if ($this == $elementType) {
            return true;
        } else {
            $baseTypes = ModelUtil::calculateAllBaseTypes($elementType);
            return in_array($this, $baseTypes);
        }
    }

    public function getAllAttributes(): array
    {
        $allAttributes = array_merge([], $this->getAttributes());
        $baseTypes = ModelUtil::calculateAllBaseTypes($this);
        foreach ($baseTypes as $baseType) {
            $allAttributes = array_merge($allAttributes, $baseType->getAttributes());
        }
        return $allAttributes;
    }

    public function getAttribute(string $attributeName): ?AttributeInterface
    {
        foreach ($this->getAllAttributes() as $attribute) {
            if ($attribute->getAttributeName() == $attributeName) {
                return $attribute;
            }
        }
        return null;
    }

    public function getChildElementCollection(
        ModelElementTypeInterface $childElementType
    ): ChildElementCollectionInterface {
        foreach ($this->getChildElementCollections() as $childElementCollection) {
            if ($childElementType == $childElementCollection->getChildElementType($this->model)) {
                return $childElementCollection;
            }
        }
        return [];
    }
}
