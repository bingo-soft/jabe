<?php

namespace BpmPlatform\Engine\Variable\Value\Builder;

use BpmPlatform\Engine\Variable\Value\SerializationDataFormatInterface;

interface ObjectValueBuilderInterface extends TypedValueBuilderInterface
{
    public function serializationDataFormat($dataFormatName): ObjectValueBuilderInterface;
}
