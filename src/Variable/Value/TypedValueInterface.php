<?php

namespace Jabe\Variable\Value;

use Jabe\Variable\Type\ValueTypeInterface;

interface TypedValueInterface extends \Serializable
{
    /**
     * The actual value. May be null in case the value is null.
     *
     * @return mixed the value
     */
    public function getValue();

    /**
     * The type of the value. See ValueType for a list of built-in ValueTypes.
     * @return ValueTypeInterface the type of the value.
     */
    public function getType(): ?ValueTypeInterface;

    /**
     * Indicator for transience of the value
     * @return isTransient
     */
    public function isTransient(): bool;
}
