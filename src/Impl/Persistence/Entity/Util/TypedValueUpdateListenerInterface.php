<?php

namespace Jabe\Impl\Persistence\Entity\Util;

use Jabe\Variable\Value\TypedValueInterface;

interface TypedValueUpdateListenerInterface
{
    /**
     * Called when an implicit update to a typed value is detected
     *
     * @param updatedValue
     */
    public function onImplicitValueUpdate(TypedValueInterface $updatedValue): void;
}
