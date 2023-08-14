<?php

namespace Jabe\Variable\Impl\Type;

use Jabe\Variable\Type\PrimitiveValueTypeInterface;
use Jabe\Variable\Value\TypedValueInterface;

abstract class PrimitiveValueTypeImpl extends AbstractValueTypeImpl implements PrimitiveValueTypeInterface
{
    protected $phpType;

    public function __construct(?string $name = null, ?string $phpType = null)
    {
        if ($name === null) {
            $name = strtolower($phpType);
        }
        parent::__construct($name);
        $this->phpType = $phpType;
    }

    public function getPhpType(): ?string
    {
        return $this->phpType;
    }

    public function isPrimitiveValueType(): bool
    {
        return true;
    }

    public function __toString()
    {
        return "PrimitiveValueType[" . $this->getName() . "]";
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->getName(),
            'phpType' => $this->phpType
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->phpType = $data['phpType'];
    }

    public function getValueInfo(TypedValueInterface $typedValue): array
    {
        $result = [];
        if ($typedValue->isTransient()) {
            $result[self::VALUE_INFO_TRANSIENT] = $typedValue->isTransient();
        }
        return $result;
    }
}
