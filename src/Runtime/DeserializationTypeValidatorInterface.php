<?php

namespace Jabe\Runtime;

interface DeserializationTypeValidatorInterface
{
    public function validate(?string $className): bool;
}
