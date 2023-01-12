<?php

namespace Jabe\Repository;

class ResourceTypes implements ResourceTypeInterface
{
    private static $REPOSITORY;

    private static $RUNTIME;

    private static $HISTORY;

    public static function repository(): ResourceTypeInterface
    {
        if (self::$REPOSITORY === null) {
            self::$REPOSITORY = new ResourceTypes('REPOSITORY', 1);
        }
        return self::$REPOSITORY;
    }

    public static function runtime(): ResourceTypeInterface
    {
        if (self::$RUNTIME === null) {
            self::$RUNTIME = new ResourceTypes('RUNTIME', 2);
        }
        return self::$RUNTIME;
    }

    public static function history(): ResourceTypeInterface
    {
        if (self::$HISTORY === null) {
            self::$HISTORY = new ResourceTypes('HISTORY', 3);
        }
        return self::$HISTORY;
    }

    // implmentation //////////////////////////

    private $name;
    private $id;

    private function __construct(?string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getValue(): int
    {
        return $this->id;
    }

    public static function forName(?string $name): ?ResourceTypeInterface
    {
        switch (strtolower($name)) {
            case "repository":
                return self::repository();
            case "runtime":
                return self::runtime();
            case "history":
                return self::history();
            default:
                return null;
        }
    }
}
