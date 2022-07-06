<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Type\PrimitiveValueTypeInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

abstract class PrimitiveValueTypeImpl extends AbstractValueTypeImpl implements PrimitiveValueTypeInterface
{
    protected $phpType;

    public function __construct(?string $name = null, string $phpType)
    {
        if ($name === null) {
            $name = strtolower($phpType);
        }
        parent::__construct($name);
        $this->phpType = $phpType;
    }

    public function getPhpType(): string
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

    public function serialize()
    {
        return json_encode([
            'name' => $this->getName(),
            'phpType' => $this->phpType
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->phpType = $json->phpType;
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
