<?php

namespace BpmPlatform\Model\Xml\Impl\Instance;

use BpmPlatform\Model\Xml\{
    ModelBuilder
};
use BpmPlatform\Model\Xml\Impl\{
    ModelInstanceImpl
};
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Instance\{
    DomElementInterface,
    ModelElementInstanceInterface
};
use BpmPlatform\Model\Xml\Type\{
    ModelElementTypeInterface
};
use BpmPlatform\Model\Xml\Exception\ModelException;

class ModelElementInstanceImpl implements ModelElementInstanceInterface
{
    protected $modelInstance;

    private $domElement;

    private $elementType;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(ModelElementInstanceInterface::class, "")->abstractType();
        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        $this->domElement = $instanceContext->getDomElement();
        $this->modelInstance = $instanceContext->getModel();
        $this->elementType = $instanceContext->getModelType();
    }

    public function getDomElement(): ?DomElementInterface
    {
        return $this->domElement;
    }

    public function getModelInstance(): ModelInstanceImpl
    {
        return $this->modelInstance;
    }

    public function getParentElement(): ?ModelElementInstanceInterface
    {
        $parentElement = $this->domElement->getParentElement();
        if ($parentElement != null) {
            return ModelUtil::getModelElement($parentElement, $this->modelInstance);
        } else {
            return null;
        }
    }

    public function getElementType(): ModelElementTypeInterface
    {
        return $this->elementType;
    }

    public function getAttributeValue(string $attributeName): ?string
    {
        return $this->domElement->getAttribute(null, $attributeName);
    }

    public function getAttributeValueNs(string $namespaceUri, string $attributeName): ?string
    {
        return $this->domElement->getAttribute($namespaceUri, $attributeName);
    }

    public function setAttributeValue(
        string $attributeName,
        string $xmlValue,
        bool $isIdAttribute = false,
        bool $withReferenceUpdate = true
    ): void {
        $oldValue = $this->getAttributeValue($attributeName);
        if ($isIdAttribute) {
            $this->domElement->setIdAttribute(null, $attributeName, $xmlValue);
        } else {
            $this->domElement->setAttribute(null, $attributeName, $xmlValue, $isIdAttribute);
        }
        $attribute = $this->elementType->getAttribute($attributeName);
        if ($attribute != null && $withReferenceUpdate) {
            $attribute->updateIncomingReferences($this, $xmlValue, $oldValue);
        }
    }

    public function setAttributeValueNs(
        string $namespaceUri,
        string $attributeName,
        string $xmlValue,
        bool $isIdAttribute = false,
        bool $withReferenceUpdate = true
    ): void {
        $namespaceForSetting = $this->determineNamespace($namespaceUri, $attributeName);
        $oldValue = $this->getAttributeValueNs($namespaceForSetting, $attributeName);
        if ($isIdAttribute) {
            $this->domElement->setIdAttribute($namespaceForSetting, $attributeName, $xmlValue);
        } else {
            $this->domElement->setAttribute($namespaceForSetting, $attributeName, $xmlValue, false);
        }
        $attribute = $this->elementType->getAttribute($attributeName);
        if ($attribute != null && $withReferenceUpdate) {
            $attribute->updateIncomingReferences($this, $xmlValue, $oldValue);
        }
    }

    private function determineNamespace(string $intendedNamespace, string $attributeName): string
    {
        $isSetInIntendedNamespace = $this->getAttributeValueNs($intendedNamespace, $attributeName) != null;
        if ($isSetInIntendedNamespace) {
            return $intendedNamespace;
        } else {
            $alternativeNamespaces = $this->modelInstance->getModel()->getAlternativeNamespaces($intendedNamespace);
            if (!empty($alternativeNamespaces)) {
                foreach ($alternativeNamespaces as $alternativeNamespace) {
                    if ($this->getAttributeValueNs($alternativeNamespace, $attributeName) != null) {
                        return $alternativeNamespace;
                    }
                }
            }
            return $intendedNamespace;
        }
    }

    public function removeAttribute(string $attributeName): void
    {
        $attribute = $this->elementType->getAttribute($attributeName);
        if ($attribute != null) {
            $identifier = $attribute->getValue($this);
            if ($identifier != null) {
                $attribute->unlinkReference($this, $identifier);
            }
        }
        $this->domElement->removeAttribute(null, $attributeName);
    }

    public function removeAttributeNs(string $namespaceUri, string $attributeName): void
    {
        $attribute = $this->elementType->getAttribute($attributeName);
        if ($attribute != null) {
            $identifier = $attribute->getValue($this);
            if ($identifier != null) {
                $attribute->unlinkReference($this, $identifier);
            }
        }
        $this->domElement->removeAttribute($namespaceUri, $attributeName);
    }

    public function getTextContent(): string
    {
        return trim($this->getRawTextContent());
    }

    public function setTextContent(string $textContent): void
    {
        $this->domElement->setTextContent($textContent);
    }

    public function getRawTextContent(): string
    {
        return $this->domElement->getTextContent();
    }

    public function getUniqueChildElementByNameNs(
        string $namespaceUri,
        string $elementName
    ): ?ModelElementInstanceInterface {
        $model = $this->modelInstance->getModel();
        $childElements = $this->domElement->getChildElementsByNameNs(
            [$namespaceUri, ...$model->getAlternativeNamespaces($namespaceUri)],
            $elementName
        );
        if (!empty($childElements)) {
            return ModelUtil::getModelElement($childElements[0], $this->modelInstance);
        } else {
            return null;
        }
    }

    public function getUniqueChildElementByType(
        string $elementType
    ): ?ModelElementInstanceInterface {
        $childElements = $this->domElement->getChildElementsByType($this->modelInstance, $elementType);
        if (!empty($childElements)) {
            return ModelUtil::getModelElement($childElements[0], $this->modelInstance);
        } else {
            return null;
        }
    }

    public function setUniqueChildElementByNameNs(ModelElementInstanceInterface $newChild): void
    {
        ModelUtil::ensureInstanceOf($newChild, ModelElementInstanceImpl::class);
        $newChildElement = $newChild;
        $childElement =  $newChildElement->getDomElement();
        $existingChild = $this->getUniqueChildElementByNameNs(
            $childElement->getNameSpaceURI(),
            $childElement->getLocalName()
        );
        if ($existingChild == null) {
            $this->addChildElement($newChild);
        } else {
            $this->replaceChildElement($existingChild, $newChildElement);
        }
    }

    public function replaceChildElement(
        ModelElementInstanceInterface $existingChild,
        ModelElementInstanceInterface $newChild
    ): void {
        $existingChildDomElement = $existingChild->getDomElement();
        $newChildDomElement = $newChild->getDomElement();
        $existingChild->unlinkAllChildReferences();
        $this->updateIncomingReferences($existingChild, $newChild);
        $this->domElement->replaceChild($newChildDomElement, $existingChildDomElement);
        $newChild->updateAfterReplacement();
    }

    private function updateIncomingReferences(
        ModelElementInstanceInterface $oldInstance,
        ModelElementInstanceInterface $newInstance
    ): void {
        $oldId = $oldInstance->getAttributeValue("id");
        $newId = $newInstance->getAttributeValue("id");

        if ($oldId == null || $newId == null) {
            return;
        }

        $attributes = $oldInstance->getElementType()->getAllAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->isIdAttribute()) {
                foreach ($attribute->getIncomingReferences() as $incomingReference) {
                    $incomingReference->referencedElementUpdated($newInstance, $oldId, $newId);
                }
            }
        }
    }

    public function replaceWithElement(ModelElementInstanceInterface $newElement): void
    {
        $parentElement = $this->getParentElement();
        if ($parentElement != null) {
            $parentElement->replaceChildElement($this, $newElement);
        } else {
            throw new ModelException("Unable to remove replace without parent");
        }
    }

    public function addChildElement(ModelElementInstanceInterface $newChild): void
    {
        ModelUtil::ensureInstanceOf($newChild, ModelElementInstanceImpl::class);
        $elementToInsertAfter = $this->findElementToInsertAfter($newChild);
        $this->insertElementAfter($newChild, $elementToInsertAfter);
    }

    public function removeChildElement(ModelElementInstanceInterface $child): bool
    {
        $child->unlinkAllReferences();
        $child->unlinkAllChildReferences();
        return $this->domElement->removeChild($child->getDomElement());
    }

    /**
     * @param mixed $childElementType
     */
    public function getChildElementsByType($childElementType): array
    {
        if ($childElementType instanceof ModelElementTypeInterface) {
            $instances = [];
            foreach ($childElementType->getExtendingTypes() as $extendingType) {
                $instances = array_merge($instances, $this->getChildElementsByType($extendingType));
            }
            $model = $this->modelInstance->getModel();
            $alternativeNamespaces = $model->getAlternativeNamespaces($childElementType->getTypeNamespace());
            $elements = $this->domElement->getChildElementsByNameNs(
                [$childElementType->getTypeNamespace(), ...$alternativeNamespaces],
                $childElementType->getTypeName()
            );
            $instances = array_merge($instances, ModelUtil::getModelElementCollection($elements, $this->modelInstance));
            return $instances;
        } elseif (
            is_subclass_of($childElementType, ModelElementInstanceImpl::class) ||
            is_subclass_of($childElementType, ModelElementInstanceInterface::class)
        ) {
            return $this->getChildElementsByType($this->getModelInstance()->getModel()->getType($childElementType));
        }
        return [];
    }

    private function findElementToInsertAfter(
        ModelElementInstanceInterface $elementToInsert
    ): ?ModelElementInstanceInterface {
        $childElementTypes = $this->elementType->getAllChildElementTypes();
        $childDomElements = $this->domElement->getChildElements();
        $childElements = ModelUtil::getModelElementCollection($childDomElements, $this->modelInstance);
        $insertAfterElement = null;
        $newElementTypeIndex = ModelUtil::getIndexOfElementType($elementToInsert, $childElementTypes);
        foreach ($childElements as $childElement) {
            $childElementTypeIndex = ModelUtil::getIndexOfElementType($childElement, $childElementTypes);
            if ($newElementTypeIndex >= $childElementTypeIndex) {
                $insertAfterElement = $childElement;
            } else {
                break;
            }
        }
        return $insertAfterElement;
    }

    public function insertElementAfter(
        ModelElementInstanceInterface $elementToInsert,
        ?ModelElementInstanceInterface $insertAfterElement
    ): void {
        if ($insertAfterElement == null || $insertAfterElement->getDomElement() == null) {
            $this->domElement->insertChildElementAfter($elementToInsert->getDomElement(), null);
        } else {
            $this->domElement->insertChildElementAfter(
                $elementToInsert->getDomElement(),
                $insertAfterElement->getDomElement()
            );
        }
    }

    public function updateAfterReplacement(): void
    {
        //
    }

    private function unlinkAllReferences(): void
    {
        $attributes = $this->elementType->getAllAttributes();
        foreach ($attributes as $attribute) {
            $identifier = $attribute->getValue($this);
            if ($identifier != null) {
                $attribute->unlinkReference($this, $identifier);
            }
        }
    }

    private function unlinkAllChildReferences(): void
    {
        $childElementTypes = $this->elementType->getAllChildElementTypes();
        foreach ($childElementTypes as $type) {
            $childElementsForType = $this->getChildElementsByType($type);
            foreach ($childElementsForType as $childElement) {
                $childElement->unlinkAllReferences();
            }
        }
    }

    public function equals(?ModelElementInstanceInterface $obj): bool
    {
        if ($obj == null) {
            return false;
        } else {
            return $obj->domElement->equals($this->domElement);
        }
    }
}
