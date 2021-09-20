<?php

namespace BpmPlatform\Model\Xml\Impl;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\Exception\ModelException;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;

class ModelImpl implements ModelInterface
{
    private $typesByName = [];
    private $typesByClass = [];
    private $modelName;

    protected $actualNsToAlternative = [];
    protected $alternativeNsToActual = [];

    public function __construct(string $modelName)
    {
        $this->modelName = $modelName;
    }


    public function declareAlternativeNamespace(string $alternativeNs, string $actualNs): void
    {
        if (!array_key_exists($actualNs, $this->actualNsToAlternative)) {
            $alternativeNamespaces = [];
            $this->actualNsToAlternative[$actualNs] = [];
        } else {
            $alternativeNamespaces = $this->actualNsToAlternative[$actualNs];
        }
        if (!in_array($alternativeNs, $alternativeNamespaces)) {
            $this->actualNsToAlternative[$actualNs] = [$alternativeNs, ...$this->actualNsToAlternative[$actualNs]];
            $this->alternativeNsToActual[$alternativeNs] = $actualNs;
        }
    }

    public function undeclareAlternativeNamespace(string $alternativeNs): void
    {
        if (!array_key_exists($alternativeNs, $this->alternativeNsToActual)) {
            return;
        }
        $actual = $this->alternativeNsToActual[$alternativeNs];

        unset($this->alternativeNsToActual[$alternativeNs]);
        foreach ($this->actualNsToAlternative[$actual] as $key => $value) {
            if ($value == $alternativeNs) {
                unset($this->actualNsToAlternative[$actual][$key]);
                break;
            }
        }
    }

    public function getAlternativeNamespace(string $actualNs): ?string
    {
        $alternatives = $this->getAlternativeNamespaces($actualNs);
        if (empty($alternatives)) {
            return null;
        } elseif (count($alternatives) == 1) {
            return $alternatives[0];
        } else {
            throw new ModelException("There is more than one alternative namespace registered");
        }
    }

    public function getAlternativeNamespaces(string $actualNs): array
    {
        if (array_key_exists($actualNs, $this->actualNsToAlternative)) {
            return $this->actualNsToAlternative[$actualNs];
        }
        return [];
    }

    public function getTypes(): array
    {
        return array_values($this->typesByName);
    }

    public function getType(string $instanceClass): ?ModelElementTypeInterface
    {
        if (array_key_exists($instanceClass, $this->typesByClass)) {
            return $this->typesByClass[$instanceClass];
        } else {
            return null;
        }
    }

    public function getTypeForName(?string $namespaceUri, string $typeName): ?ModelElementTypeInterface
    {
        $name = ModelUtil::getQName($namespaceUri, $typeName);
        if (array_key_exists(strval($name), $this->typesByName)) {
            return $this->typesByName[strval($name)];
        }
        return null;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function getActualNamespace(string $alternativeNs): ?string
    {
        if (array_key_exists($alternativeNs, $this->alternativeNsToActual)) {
            return $this->alternativeNsToActual[$alternativeNs];
        } else {
            return null;
        }
    }

    public function registerType(ModelElementTypeInterface $modelElementType, string $instanceType): void
    {
        $qName = ModelUtil::getQName($modelElementType->getTypeNamespace(), $modelElementType->getTypeName());
        $this->typesByName[strval($qName)] = $modelElementType;
        $this->typesByClass[$instanceType] = $modelElementType;
    }
}
