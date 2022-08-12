<?php

namespace Jabe\Authorization;

/**
 * A permission represents an authorization to interact with a given
 * resource in a specific way.
 *
 */
interface PermissionInterface
{
    /*
     * Returns the name of the permission, ie. 'UPDATE'
     */
    public function getName(): string;

    /*
     * Returns the unique numeric value of the permission.
     * Must be a power of 2. ie 2^0, 2^1, 2^2, 2^3, 2^4 ...
     *
     * @return int
     */
    public function getValue(): int;

    /*
     * Returns the resource types which are allowed for this permission
     *
     * @return ResourceInterface[]
     */
    public function getTypes(): array;
}
