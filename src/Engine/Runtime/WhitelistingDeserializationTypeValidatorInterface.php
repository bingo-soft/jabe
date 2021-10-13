<?php

namespace BpmPlatform\Engine\Runtime;

interface WhitelistingDeserializationTypeValidatorInterface extends DeserializationTypeValidatorInterface
{
    /** Set the allowed class names */
    public function setAllowedClasses(string $deserializationAllowedClasses): void;

    /** Set the allowed package names */
    public function setAllowedPackages(string $deserializationAllowedPackages): void;
}
