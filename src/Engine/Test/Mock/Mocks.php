<?php

namespace Jabe\Engine\Test\Mock;

class Mocks
{
    private static $mockContainer = [];

    public static function getMocks(): array
    {
        return self::$mockContainer;
    }

    /**
     * This method lets you register a mock object. Make sure to register the
     * {@link MockExpressionManager} with your process engine configuration.
     *
     * @param key
     *          the key under which the mock object will be registered
     * @param value
     *          the mock object
     */
    public static function register(string $key, $value): void
    {
        self::$mockContainer[$key] = $value;
    }

    /**
     * This method returns the mock object registered under the provided key or
     * null if there is no object for the provided key.
     *
     * @param key
     *          the key of the requested object
     * @return the mock object registered under the provided key or null if there
     *         is no object for the provided key
     */
    public static function get(string $key)
    {
        if (array_key_exists($key, self::$mockContainer)) {
            return self::$mockContainer[$key];
        }
        return null;
    }

    /**
     * This method resets the internal map of mock objects.
     */
    public static function reset(): void
    {
        if (!empty(self::$mockContainer)) {
            self::$mockContainer = [];
        }
    }
}
