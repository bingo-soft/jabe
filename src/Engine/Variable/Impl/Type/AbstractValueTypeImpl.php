<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Type\ValueTypeInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

abstract class AbstractValueTypeImpl implements ValueTypeInterface
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function serialize()
    {
        return json_encode([
            'name' => $this->name
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
    }

    public function isAbstract(): bool
    {
        return false;
    }

    public function getParent(): ?ValueTypeInterface
    {
        return null;
    }

    public function canConvertFromTypedValue(?TypedValueInterface $typedValue): bool
    {
        return false;
    }

    public function convertFromTypedValue(TypedValueInterface $typedValue): TypedValueInterface
    {
        throw new \InvalidArgumentException(
            sprintf(
                "The type %s supports no conversion from type: %s",
                $this->getName(),
                $typedValue->getType()->getName()
            )
        );
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->name === null) {
            if ($obj->name !== null) {
                return false;
            }
        } elseif ($this->name != $obj->name) {
            return false;
        }
        return true;
    }

    protected function isTransient(?array $valueInfo): bool
    {
        $isTransient = null;
        if ($valueInfo !== null && array_key_exists(self::VALUE_INFO_TRANSIENT, $valueInfo)) {
            $isTransient = $valueInfo[self::VALUE_INFO_TRANSIENT];
            if (is_bool($isTransient)) {
                return $isTransient;
            } else {
                throw new \InvalidArgumentException("The property 'transient' should have a value of type 'boolean'.");
            }
        }
        return false;
    }
}
