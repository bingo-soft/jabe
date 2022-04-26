<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

interface ResolverInterface
{
    /**
     * Allows checking whether there is currently an object bound to the key.
     *
     * @param key the key to check
     * @return bool - true if there is currently an object bound to the key. False otherwise.
     */
    public function containsKey($key): bool;

    /**
     * Returns the object currently bound to the key or false if no object is currently bound
     * to the key
     *
     * @param key the key of the object to retrieve.
     * @return the object currently bound to the key or 'null' if no object is currently bound to the key.
     */
    public function get($key);

    /**
     * Returns the set of key that can be resolved using this resolver.
     * @return the set of keys that can be resolved by this resolver.
     */
    public function keySet(): array;
}
