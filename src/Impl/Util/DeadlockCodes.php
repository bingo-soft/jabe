<?php

namespace Jabe\Impl\Util;

class DeadlockCodes
{
    private static $POSTGRES;
    private static $MARIADB_MYSQL;
    private static $MSSQL;
    private static $DB2;
    private static $ORACLE;

    private $errorCode;
    private $sqlState;

    public static function postgres(): DeadlockCodes
    {
        if (self::$POSTGRES === null) {
            self::$POSTGRES = new DeadlockCodes(0, "40P01");
        }
        return self::$POSTGRES;
    }

    public static function mariadbMysql(): DeadlockCodes
    {
        if (self::$MARIADB_MYSQL === null) {
            self::$MARIADB_MYSQL = new DeadlockCodes(1213, "40001");
        }
        return self::$MARIADB_MYSQL;
    }

    public static function mssql(): DeadlockCodes
    {
        if (self::$MSSQL === null) {
            self::$MSSQL = new DeadlockCodes(1205, "40001");
        }
        return self::$MSSQL;
    }

    public static function db2(): DeadlockCodes
    {
        if (self::$DB2 === null) {
            self::$DB2 = new DeadlockCodes(-911, "40001");
        }
        return self::$DB2;
    }

    public static function oracle(): DeadlockCodes
    {
        if (self::$ORACLE === null) {
            self::$ORACLE = new DeadlockCodes(60, "61000");
        }
        return self::$ORACLE;
    }

    private function __construct(int $errorCode, ?string $sqlState)
    {
        $this->errorCode = $errorCode;
        $this->sqlState = $sqlState;
    }

    public function equals(int $errorCode, ?string $sqlState): bool
    {
        return $this->errorCode == $errorCode && $this->sqlState == $sqlState;
    }
}
