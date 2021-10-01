<?php

namespace Tests\Xml\Test;

class AttributeAssumption
{
    public $attributeName;
    public $namespace;
    public $isIdAttribute;
    public $isRequired;
    public $defaultValue;

    public function __construct(
        ?string $namespace,
        string $attributeName,
        bool $isIdAttribute = false,
        bool $isRequired = false,
        ?string $defaultValue = null
    ) {
        $this->attributeName = $attributeName;
        $this->namespace = $namespace;
        $this->isIdAttribute = $isIdAttribute;
        $this->isRequired = $isRequired;
        $this->defaultValue = $defaultValue;
    }
}
