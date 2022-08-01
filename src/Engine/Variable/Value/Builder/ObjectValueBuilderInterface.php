<?php

namespace Jabe\Engine\Variable\Value\Builder;

interface ObjectValueBuilderInterface extends TypedValueBuilderInterface
{
    public function serializationDataFormat($dataFormatName): ObjectValueBuilderInterface;
}
