<?php

namespace Jabe\Impl\Db;

class RowBounds
{
    public const NO_ROW_OFFSET = 0;
    public const NO_ROW_LIMIT = PHP_INT_MAX;
    private static $DEFAULT;

    private $offset;
    private $limit;

    public function __construct(int $offset = null, int $limit = null)
    {
        if ($offset !== null) {
            $this->offset = $offset;
        } else {
            $this->offset = self::NO_ROW_OFFSET;
        }
        if ($limit !== null) {
            $this->limit = $limit;
        } else {
            $this->limit = self::NO_ROW_LIMIT;
        }
    }

    public static function default(): RowBounds
    {
        if (self::$DEFAULT === null) {
            self::$DEFAULT = new RowBounds();
        }
        return self::$DEFAULT;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
