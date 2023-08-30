<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\ProcessEngineException;
use Jabe\Impl\Context\Context;
use Jabe\Variable\Value\TypedValueInterface;

class DefaultVariableSerializers implements VariableSerializersInterface
{
    protected $serializerList = [];
    protected $serializerMap = [];

    public function __construct(?DefaultVariableSerializers $serializers = null)
    {
        if ($serializers !== null) {
            $this->serializerList = $serializers->serializerList;
            $this->serializerMap = $serializers->serializerMap;
        }
    }

    public function getSerializerByName(?string $serializerName): ?TypedValueSerializerInterface
    {
        if (array_key_exists($serializerName, $this->serializerMap)) {
            return $this->serializerMap[$serializerName];
        }
        return null;
    }

    public function findSerializerForValue(TypedValueInterface $value, ?VariableSerializerFactoryInterface $fallBackSerializerFactory = null): ?TypedValueSerializerInterface
    {
        $defaultSerializationFormat = Context::getProcessEngineConfiguration()->getDefaultSerializationFormat();

        $matchedSerializers = [];

        $type = $value->getType();
        if ($type !== null && $type->isAbstract()) {
            throw new ProcessEngineException("Cannot serialize value of abstract type " . $type->getName());
        }

        foreach ($this->serializerList as $serializer) {
            if ($type === null || $serializer->getType() == $type) {
                if ($serializer->canHandle($value)) {
                    $matchedSerializers[] = $serializer;
                    if ($serializer->getType()->isPrimitiveValueType()) {
                        break;
                    }
                }
            }
        }

        if (empty($matchedSerializers)) {
            if ($fallBackSerializerFactory !== null) {
                $serializer = $fallBackSerializerFactory->getSerializer($value);
                if ($serializer !== null) {
                    return $serializer;
                }
            }

            throw new ProcessEngineException("Cannot find serializer for value '" . $value . "'.");
        } elseif (count($matchedSerializers) == 1) {
            return $matchedSerializers[0];
        } else {
            // ambiguous match, use default serializer
            if ($defaultSerializationFormat !== null) {
                foreach ($matchedSerializers as $typedValueSerializer) {
                    if ($defaultSerializationFormat == $typedValueSerializer->getSerializationDataformat()) {
                        return $typedValueSerializer;
                    }
                }
            }
            // no default serialization dataformat defined or default dataformat cannot serialize this value => use first serializer
            return $matchedSerializers[0];
        }
    }

    public function addSerializer(TypedValueSerializerInterface $serializer, ?int $index = null)
    {
        if ($index === null) {
            $index = count($this->serializerList);
        }
        $this->serializerList[] = $serializer;
        $this->serializerMap[$serializer->getName()] = $serializer;
    }

    public function setSerializerList(array $serializerList): void
    {
        $this->serializerList = [];
        $this->serializerMap = [];
        foreach ($serializerList as $serializer) {
            $this->serializerMap[$serializer->getName()] = $serializer;
        }
    }

    public function getSerializerIndex(TypedValueSerializerInterface $serializer): int
    {
        $name = $serializer->getName();
        if (array_key_exists($name, $this->serializerMap)) {
            foreach ($this->serializerList as $key => $curSerializer) {
                if ($curSerializer->getName() == $name) {
                    return $key;
                }
            }
        }
        return -1;
    }

    public function getSerializerIndexByName(?string $serializerName): int
    {
        $serializer = $this->serializerMap[$serializerName];
        return $this->getSerializerIndex($serializer);
    }

    public function removeSerializer(TypedValueSerializerInterface $serializer): VariableSerializersInterface
    {
        $index = $this->getSerializerIndex($serializer);
        if ($index != -1) {
            unset($this->serializerList[$index]);
            unset($this->serializerMap[$serializer->getName()]);
        }
        return $this;
    }

    public function join(VariableSerializersInterface $other): VariableSerializersInterface
    {
        $copy = new DefaultVariableSerializers();

        // "other" serializers override existing ones if their names match
        foreach ($this->serializerList as $thisSerializer) {
            $serializer = $other->getSerializerByName($thisSerializer->getName());

            if ($serializer === null) {
                $serializer = $thisSerializer;
            }

            $copy->addSerializer($serializer);
        }

        // add all "other" serializers that did not exist before to the end of the list
        foreach ($other->getSerializers() as $otherSerializer) {
            if (!array_key_exists($otherSerializer->getName(), $copy->serializerMap)) {
                $copy->addSerializer($otherSerializer);
            }
        }
        return $copy;
    }

    public function getSerializers(): array
    {
        return $this->serializerList;
    }
}
