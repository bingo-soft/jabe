<?php

namespace Jabe\Engine\Impl\Variable\Serializer;

interface VariableSerializerFactoryInterface
{
    public function getSerializer($serializerName): TypedValueSerializerInterface;
}
