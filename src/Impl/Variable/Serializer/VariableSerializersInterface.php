<?php

namespace Jabe\Impl\Variable\Serializer;

use Jabe\Variable\Value\TypedValueInterface;

interface VariableSerializersInterface
{
    /**
     * Selects the TypedValueSerializer which should be used for persisting a VariableValue.
     *
     * @param value the value to persist
     * @param fallBackSerializerFactory a factory to build a fallback serializer in case no suiting serializer
     *   can be determined. If this factory is not able to build serializer either, an exception is thrown. May be null
     * @return TypedValueSerializerInterface he VariableValueserializer selected for persisting the value or 'null' in case no serializer can be found
     */
    public function findSerializerForValue(TypedValueInterface $value, ?VariableSerializerFactory $fallBackSerializerFactory = null): TypedValueSerializerInterface;

    /**
     *
     * @return TypedValueSerializerInterface the serializer for the given serializerName name.
     * Returns null if no type was found with the name.
     */
    public function getSerializerByName(string $serializerName): TypedValueSerializerInterface;

    public function addSerializer(TypedValueSerializerInterface $serializer, ?int $index = null): VariableSerializersInterface;

    public function removeSerializer(TypedValueSerializerInterface $serializer): VariableSerializersInterface;

    public function getSerializerIndex(TypedValueSerializerInterface $serializer): ?int;

    public function getSerializerIndexByName(string $serializerName): ?int;

    /**
    * Merges two VariableSerializers instances into one. Implementations may apply
    * different merging strategies.
    */
    public function join(VariableSerializersInterface $other): VariableSerializersInterface;

    /**
    * Returns the serializers as a list in the order of their indices.
    */
    public function getSerializers(): array;
}
