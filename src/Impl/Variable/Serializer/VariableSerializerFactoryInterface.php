<?php

namespace Jabe\Impl\Variable\Serializer;

interface VariableSerializerFactoryInterface
{
    public function getSerializer($serializerName): TypedValueSerializerInterface;
}
