<?php

namespace BpmPlatform\Model\Xml\Impl\Util;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\Exception\ModelException;
use BpmPlatform\Model\Xml\Impl\ModelInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;
use BpmPlatform\Model\Xml\Impl\Type\Attribute\StringAttribute;
use BpmPlatform\Model\Xml\Instance\{
    DomElementInterface,
    ModelElementInstanceInterface
};
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;

class ModelUtil
{
    public const ID_ATTRIBUTE_NAME = "id";

    /**
     * @return mixed
     */
    public static function getModelElement(
        DomElementInterface $domElement,
        ModelInstanceImpl $modelInstance,
        ?ModelElementTypeImpl $modelType = null,
        ?string $namespaceUri = null
    ) {
        if ($modelType == null && $namespaceUri == null) {
            $modelElement = $domElement->getModelElementInstance();

            if ($modelElement == null) {
                $modelType = self::getModelElement($domElement, $modelInstance, null, $domElement->getNamespaceURI());
                $modelElement = $modelType->newInstance($modelInstance, $domElement);
                $domElement->setModelElementInstance($modelElement);
            }
            return $modelElement;
        } elseif ($modelType != null) {
            $modelElement = $domElement->getModelElementInstance();

            if ($modelElement == null) {
                $modelElement = $modelType->newInstance($modelInstance, $domElement);
                $domElement->setModelElementInstance($modelElement);
            }
            return $modelElement;
        } elseif ($namespaceUri != null) {
            $localName = $domElement->getLocalName();
            $modelType = $modelInstance->getModel()->getTypeForName($namespaceUri, $localName);
            if ($modelType == null) {
                $model = $modelInstance->getModel();
                $actualNamespaceUri = $model->getActualNamespace($namespaceUri);
                if ($actualNamespaceUri != null) {
                    $modelType = self::getModelElement($domElement, $modelInstance, null, $actualNamespaceUri);
                } else {
                    $modelType = $modelInstance->registerGenericType($namespaceUri, $localName);
                }
            }

            return $modelType;
        }

        return null;
    }

    public static function getQName(?string $namespaceUri, string $localName): QName
    {
        return new QName($namespaceUri, $localName);
    }

    /**
     * @param mixed $instance
     * @param string $type
     */
    public static function ensureInstanceOf($instance, string $type): void
    {
        if (!($instance instanceof $type)) {
            throw new ModelException(stringf("Object is not instance of type ", $type));
        }
    }

    public static function valueAsBoolean(string $rawValue): bool
    {
        return json_decode($rawValue);
    }

    public static function valueAsInteger(string $rawValue): int
    {
        return intval($rawValue);
    }

    public static function valueAsFloat(string $rawValue): float
    {
        return floatval($rawValue);
    }

    public static function valueAsDouble(string $rawValue): float
    {
        return floatval($rawValue);
    }

    public static function valueAsShort(string $rawValue): int
    {
        return intval($rawValue);
    }

    /**
     * @param mixed $rawValue
     */
    public static function valueAsString($rawValue): string
    {
        return strval($rawValue);
    }

    public static function getModelElementCollection(array $view, ModelInstanceImpl $model): array
    {
        $resultList = [];
        foreach ($view as $element) {
            $resultList[] = self::getModelElement($element, $model);
        }
        return $resultList;
    }

    public static function getIndexOfElementType(
        ModelElementInstanceInterface $modelElement,
        array $childElementTypes
    ): int {
        $numOfChildren = count($childElementTypes);
        for ($index = 0; $index < $numOfChildren; $index += 1) {
            $childElementType = $childElementTypes[$index];
            $instanceType = $childElementType->getInstanceType();
            if (is_a($modelElement, $instanceType)) {
                return $index;
            }
        }
        $childElementTypeNames = [];
        foreach ($childElementTypes as $childElementType) {
            $childElementTypeNames[] = $childElementType->getTypeName();
        }
        throw new ModelException(
            sprintf(
                "New child is not a valid child element type: %s; valid types are: %s",
                $modelElement->getElementType()->getTypeName(),
                implode(', ', $childElementTypeNames)
            )
        );
    }

    public static function calculateAllExtendingTypes(
        ModelInterface $model,
        array $baseTypes
    ): array {
        $allExtendingTypes = [];
        foreach ($baseTypes as $baseType) {
            $modelElementTypeImpl = $model->getType($baseType->getInstanceType());
            $modelElementTypeImpl->resolveExtendingTypes($allExtendingTypes);
        }
        return $allExtendingTypes;
    }

    public static function calculateAllBaseTypes(ModelElementTypeInterface $type): array
    {
        $baseTypes = [];
        $type->resolveBaseTypes($baseTypes);
        return $baseTypes;
    }

    public static function setNewIdentifier(
        ModelElementTypeInterface $type,
        ModelElementInstanceInterface $modelElementInstance,
        string $newId,
        bool $withReferenceUpdate
    ): void {
        $id = $type->getAttribute(self::ID_ATTRIBUTE_NAME);
        if ($id != null && $id instanceof StringAttribute && $id->isIdAttribute()) {
            $id->setValue($modelElementInstance, $newId, $withReferenceUpdate);
        }
    }

    public static function setGeneratedUniqueIdentifier(
        ModelElementTypeInterface $type,
        ModelElementInstanceInterface $modelElementInstance,
        bool $withReferenceUpdate = true
    ): void {
        self::setNewIdentifier($type, $modelElementInstance, self::getUniqueIdentifier($type), $withReferenceUpdate);
    }

    public static function getUniqueIdentifier(ModelElementTypeInterface $type): string
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        return $type->getTypeName() . '_' . $uuid;
    }
}
