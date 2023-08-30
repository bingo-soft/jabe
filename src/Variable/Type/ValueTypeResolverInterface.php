<?php

namespace Jabe\Variable\Type;

interface ValueTypeResolverInterface
{
    public function addType(ValueTypeInterface $type): void;

    public function typeForName(?string $typeName): ValueTypeInterface;

    /**
     * Returns all (transitive) sub types of the provided type
     * given they are not abstract
     *
     * @return
     */
    public function getSubTypes(ValueTypeInterface $type): array;
}
