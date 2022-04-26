<?php

namespace Jabe\Engine\Impl\Core\Variable\Scope;

use Jabe\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

class SimpleVariableInstance implements CoreVariableInstanceInterface
{
    protected $name;
    protected $value;

    public function __construct(string $name, TypedValueInterface $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getTypedValue(bool $deserialize): TypedValueInterface
    {
        return $this->value;
    }
    public function setValue(TypedValueInterface $value): void
    {
        $this->value = $value;
    }
}
