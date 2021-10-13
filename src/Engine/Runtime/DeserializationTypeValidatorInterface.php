<?php

namespace BpmPlatform\Engine\Runtime;

interface DeserializationTypeValidatorInterface
{
    public function validate(string $className): bool;
}
