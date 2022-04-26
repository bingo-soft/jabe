<?php

namespace Jabe\Engine\Runtime;

interface DeserializationTypeValidatorInterface
{
    public function validate(string $className): bool;
}
