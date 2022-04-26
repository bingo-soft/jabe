<?php

namespace Jabe\Engine\Variable\Value\Builder;

use Jabe\Engine\Variable\Value\SerializationDataFormatInterface;

interface ObjectValueBuilderInterface extends TypedValueBuilderInterface
{
    public function serializationDataFormat($dataFormatName): ObjectValueBuilderInterface;
}
