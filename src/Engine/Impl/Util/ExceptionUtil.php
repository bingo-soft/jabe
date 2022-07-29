<?php

namespace Jabe\Engine\Impl\Util;

use Doctrine\DBAL\Exception\{
    ConstraintViolationException,
    DeadlockException,
    ForeignKeyConstraintViolationException,
    ServerException
};
use Doctrine\ORM\ORMException;
use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\ByteArrayEntity;
use Jabe\Engine\Repository\ResourceTypeInterface;

class ExceptionUtil
{
    public const PERSISTENCE_EXCEPTION_MESSAGE = "An exception occurred in the " .
        "persistence layer. Please check the server logs for a detailed message and the entire " .
        "exception stack trace.";

    public static function getExceptionStacktrace($obj): ?string
    {
        if ($obj instanceof \Throwable) {
            return $obj->getTraceAsString();
        } elseif ($obj instanceof ByteArrayEntity) {
            return StringUtil::fromBytes($obj->getBytes());
        }
        return null;
    }


    public static function createJobExceptionByteArray(?string $byteArray, ResourceTypeInterface $type): ?ByteArrayEntity
    {
        return self::createExceptionByteArray("job.exceptionByteArray", $byteArray, $type);
    }

    /**
     * create ByteArrayEntity with specified name and payload and make sure it's
     * persisted
     *
     * used in Jobs and ExternalTasks
     *
     * @param name - type\source of the exception
     * @param byteArray - payload of the exception
     * @param type - resource type of the exception
     * @return persisted entity
     */
    public static function createExceptionByteArray(string $name, ?string $byteArray, ResourceTypeInterface $type): ?ByteArrayEntity
    {
        $result = null;

        if ($byteArray != null) {
            $result = new ByteArrayEntity($name, $byteArray, $type);
            Context::getCommandContext()
            ->getByteArrayManager()
            ->insertByteArray($result);
        }

        return $result;
    }

    protected static function getPersistenceCauseException(/*ServerException|ORMException*/$persistenceException): \Throwable
    {
        if (method_exists($persistenceException, "getCause")) {
            $cause = $persistenceException->getCause();
            if ($cause !== null) {
                return $cause;
            }
        } elseif (method_exists($persistenceException, "getPrevious")) {
            $cause = $persistenceException->getPrevious();
            if ($cause !== null) {
                return $cause;
            }
        }
        return $persistenceException;
    }

    public static function unwrapException(/*ProcessEngineException|ServerException|ORMException*/$exception): ?\Exception
    {
        if ($exception instanceof ProcessEngineException) {
            $cause = null;
            if (method_exists($exception, "getCause")) {
                $cause = $exception->getCause();
            } elseif (method_exists($exception, "getPrevious")) {
                $cause = $exception->getPrevious();
            }

            if ($cause instanceof ProcessEngineException) {
                $processEngineExceptionCause = null;
                if (method_exists($cause, "getCause")) {
                    $processEngineExceptionCause = $cause->getCause();
                } elseif (method_exists($cause, "getPrevious")) {
                    $processEngineExceptionCause = $cause->getPrevious();
                }

                if ($processEngineExceptionCause instanceof ServerException || $processEngineExceptionCause instanceof ORMException) {
                    return self::unwrapException($processEngineExceptionCause);
                } else {
                    return null;
                }
            } elseif ($cause instanceof ServerException || $cause instanceof ORMException) {
                return self::getPersistenceCauseException($cause);
            } else {
                return null;
            }
        } elseif ($exception instanceof ServerException || $exception instanceof ORMException) {
            return self::getPersistenceCauseException($exception);
        } else {
            return null;
        }
    }

    public static function checkValueTooLongException($exception): bool
    {
        if ($exception instanceof ProcessEngineException) {
            $sqlException = self::unwrapException($exception);
            if ($sqlException === null) {
                return false;
            } else {
                return self::checkValueTooLongException($sqlException);
            }
        } elseif ($exception instanceof ServerException || $exception instanceof ORMException) {
            $message = $exception->getMessage();
            return strpos($message, "too long") !== false ||
                strpos($message, "too large") !== false ||
                strpos($message, "TOO LARGE") !== false ||
                strpos($message, "ORA-01461") !== false ||
                strpos($message, "ORA-01401") !== false ||
                strpos($message, "data would be truncated") !== false ||
                strpos($message, "SQLCODE=-302, SQLSTATE=22001");
        } else {
            return false;
        }
    }

    public static function checkConstraintViolationException($exception): bool
    {
        if ($exception instanceof ProcessEngineException) {
            $sqlException = self::unwrapException($exception);
            if ($sqlException === null) {
                return false;
            } else {
                return self::checkConstraintViolationException($sqlException);
            }
        }

        $message = $exception->getMessage();
        return $exception instanceof ConstraintViolationException ||
            strpos($message, "constraint") !== false ||
            strpos($message, "violat") !== false ||
            strpos(strtolower($message), "duplicate") !== false ||
            strpos($message, "ORA-00001") !== false ||
            strpos($message, "SQLCODE=-803, SQLSTATE=23505");
    }

    public static function checkForeignKeyConstraintViolation(/*ProcessEngineException|ServerException|ORMException*/$exception, bool $skipPostgres): bool
    {
        if ($exception instanceof ProcessEngineException) {
            $sqlException = self::unwrapException($exception);
            if ($sqlException === null) {
                return false;
            } else {
                return self::checkForeignKeyConstraintViolation($sqlException);
            }
        }

        if ($exception instanceof ForeignKeyConstraintViolationException) {
            return true;
        }

        $message = strtolower($exception->getMessage());
        $sqlState = null;
        if (method_exists($exception, 'getSQLState')) {
            $sqlState = $exception->getSQLState();
        } else {
            //SQLSTATE
            preg_match_all('/SQLSTATE\[(\d*)\]/', $message, $matches);
            if (!empty($matches[0])) {
                $sqlState = $matches[0];
            }
        }
        $errorCode = $exception->getCode();

        if ($sqlState == '23503' && $errorCode == 0) {
            return !$skipPostgres;
        } else {
            // SqlServer
            return strpos($message, "foreign key constraint") !== false ||
                $sqlState == "23000" && $errorCode == 547 ||
                // MySql & MariaDB & PostgreSQL
                $sqlState == "23000" && $errorCode == 1452 ||
                // Oracle & H2
                strpos($message, "integrity constraint") !== false ||
                // Oracle
                $sqlState == "23000" && $errorCode == 2291 ||
                // H2
                //$sqlState == "23506" && $errorCode == 23506 ||
                // DB2
                strpos($message, "sqlstate=23503") !== false && strpos($message, "sqlcode=-530") !== false ||
                // DB2 zOS
                $sqlState == "23503" && $errorCode == -530;
        }
    }

    public static function checkVariableIntegrityViolation($exception): bool
    {
        if ($exception instanceof ProcessEngineException) {
            $sqlException = self::unwrapException($exception);
            if ($sqlException === null) {
                return false;
            } else {
                return self::checkVariableIntegrityViolation($sqlException);
            }
        }

        $message = strtolower($exception->getMessage());
        $sqlState = null;
        if (method_exists($exception, 'getSQLState')) {
            $sqlState = $exception->getSQLState();
        } else {
            //SQLSTATE
            preg_match_all('/SQLSTATE\[(\d*)\]/', $message, $matches);
            if (!empty($matches[0])) {
                $sqlState = $matches[0];
            }
        }
        $errorCode = $exception->getCode();

        // MySQL & MariaDB
        return (strpos($message, "act_uniq_variable") !== false && $sqlState == "23000" && $errorCode == 1062)
            // PostgreSQL
            || (strpos($message, "act_uniq_variable") !== false && $sqlState == "23505" && $errorCode == 0)
            // SqlServer
            || (strpos($message, "act_uniq_variable") !== false && $sqlState == "23000" && $errorCode == 2601)
            // Oracle
            || (strpos($message, "act_uniq_variable") !== false && $sqlState == "23000" && $errorCode == 1);
    }

    public static function checkDeadlockException($sqlException): bool
    {
        $sqlState = null;
        if (method_exists($sqlException, 'getSQLState')) {
            $sqlState = $sqlException->getSQLState();
        } else {
            //SQLSTATE
            preg_match_all('/SQLSTATE\[(\d*)\]/', $message, $matches);
            if (!empty($matches[0])) {
                $sqlState = $matches[0];
            }
        }
        $errorCode = $sqlException->getCode();

        return DeadlockCodes::mariadbMysql()->equals($errorCode, $sqlState) ||
            DeadlockCodes::mssql()->equals($errorCode, $sqlState) ||
            DeadlockCodes::db2()->equals($errorCode, $sqlState) ||
            DeadlockCodes::oracle()->equals($errorCode, $sqlState) ||
            DeadlockCodes::postgres()->equals($errorCode, $sqlState);
    }

    public static function findBatchExecutorException($exception)
    {
        return null;
    }

    /**
     * Pass logic, which directly calls MyBatis API. In case a MyBatis exception is thrown, it is
     * wrapped into a {@link ProcessEngineException} and never propagated directly to an Engine API
     * call. In some cases, the top-level exception and its message are shown as a response body in
     * the REST API. Wrapping all MyBatis API calls in our codebase makes sure that the top-level
     * exception is always a {@link ProcessEngineException} with a generic message. Like this, SQL
     * details are never disclosed to potential attackers.
     *
     * @param supplier which calls MyBatis API
     * @param <T> is the type of the return value
     * @return the value returned by the supplier
     * @throws ProcessEngineException which wraps the actual exception
     */
    public static function doWithExceptionWrapper(callable $supplier)
    {
        try {
            return $supplier();
        } catch (\Exception $ex) {
            throw self::wrapPersistenceException($ex);
        }
    }

    public static function wrapPersistenceException(\Exception $ex): ProcessEngineException
    {
        return new ProcessEngineException(self::PERSISTENCE_EXCEPTION_MESSAGE, $ex);
    }
}
