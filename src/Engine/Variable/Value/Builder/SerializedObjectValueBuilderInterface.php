<?php

namespace BpmPlatform\Engine\Variable\Value\Builder;

interface SerializedObjectValueBuilderInterface extends ObjectValueBuilderInterface
{
    public function serializedValue(string $value): SerializedObjectValueBuilderInterface;

    public function objectTypeName(string $typeName): SerializedObjectValueBuilderInterface;

    public function serializationDataFormat($dataFormatName): SerializedObjectValueBuilderInterface;
}
