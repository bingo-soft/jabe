<?php

namespace Jabe\Variable\Value\Builder;

interface ObjectValueBuilderInterface extends TypedValueBuilderInterface
{
    public function serializationDataFormat($dataFormatName): ObjectValueBuilderInterface;
}
