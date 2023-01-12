<?php

namespace Jabe\Repository;

interface ResourceTypeInterface
{
    /** returns the name of the resource's type */
    public function getName(): ?string;

    /** returns the unique numeric value of the type. */
    public function getValue(): int;
}
