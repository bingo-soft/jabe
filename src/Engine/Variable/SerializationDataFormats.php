<?php

namespace BpmPlatform\Engine\Variable;

use BpmPlatform\Engine\Variable\Value\SerializationDataFormatInterface;

class SerializationDataFormats implements SerializationDataFormatInterface
{
    public const PHP = "application/x-php-serialized-object";

    public const JSON = "application/json";

    public const XML = "application/xml";

    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
