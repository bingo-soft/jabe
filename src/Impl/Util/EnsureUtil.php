<?php

namespace Jabe\Impl\Util;

use Jabe\{
    ProcessEngineConfiguration,
    ProcessEngineException
};
use Jabe\Authorization\AuthorizationInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\CommandContext;

class EnsureUtil
{
    public static function ensureNotNull(?string $message, ?string $variableName, $variable = null, ...$variables): void
    {
        if ($variable === null) {
            throw self::generateException($message, $variableName, "is null");
        }
        if (is_array($variables) && count($variables) > 0) {
            foreach ($variables as $var) {
                if ($var === null) {
                    throw self::generateException($message, $variableName, "contains null value");
                }
            }
        }
    }

    public static function ensureNotEmpty(?string $message, ?string $variableName, $variable = []): void
    {
        if (empty($variable)) {
            throw self::generateException($message, $variableName, "is empty");
        }
    }

    public static function ensureEquals(?string $message, ?string $variableName, $obj1, $obj2): void
    {
        if ($obj1 != $obj2) {
            throw self::generateException($message, $variableName, "value differs from expected");
        }
    }

    public static function ensurePositive(?string $message, ?string $variableName, $value): void
    {
        if ($value <= 0) {
            throw self::generateException($message, $variableName, "is not greater than 0");
        }
    }

    public static function ensureLessThan(?string $message, ?string $variableName, $value1, $value2): void
    {
        if ($value1 >= $value2) {
            throw self::generateException($message, $variableName, "is not less than" . $value2);
        }
    }

    public static function ensureGreaterThanOrEqual(?string $message, ?string $variableName, $value1, $value2): void
    {
        if ($value1 < $value2) {
            throw self::generateException($message, $variableName, "is not greater than or equal to " . $value2);
        }
    }

    public static function ensureInstanceOf($instance, ?string $type): void
    {
        if (!($instance instanceof $type)) {
            throw new \Exception(sprintf("Object of class %s is not instance of type %s", get_class($instance), $type));
        }
    }

    public static function ensureOnlyOneNotNull(?string $message, ...$values): void
    {
        $oneNotNull = false;
        foreach ($values as $value) {
            if ($value != null) {
                if ($oneNotNull) {
                    throw self::generateException($message);
                }
                $oneNotNull = true;
            }
        }
        if (!$oneNotNull) {
            throw self::generateException($message);
        }
    }

    public static function ensureAtLeastOneNotNull(?string $message, ...$values): void
    {
        foreach ($values as $value) {
            if ($value !== null) {
                return;
            }
        }
        throw self::generateException($message);
    }

    public static function ensureAtLeastOneNotEmpty(?string $message, ...$values): void
    {
        foreach ($values as $value) {
            if (!empty($value)) {
                return;
            }
        }
        throw self::generateException($message);
    }

    public static function ensureNotContainsEmptyString(?string $message, ?string $variableName, array $variables = []): void
    {
        foreach ($variables as $value) {
            if (empty($value)) {
                throw self::generateException($message, $variableName, "contains empty string");
            }
        }
    }

    public static function ensureNotContainsNull(?string $message, ?string $variableName, array $variables = []): void
    {
        foreach ($variables as $value) {
            if ($value === null) {
                throw self::generateException($message, $variableName, "contains null");
            }
        }
    }

    public static function ensureNumberOfElements(?string $message, ?string $variableName, array $collection, int $elements): void
    {
        if (count($collection) != $elements) {
            throw self::generateException($message, $variableName, "does not have " . $elements . " elements");
        }
    }

    public static function ensureValidIndividualResourceId(?string $message, ?string $id): void
    {
        self::ensureNotNull($message, "id", $id);
        if (AuthorizationInterface::ANY === $id) {
            throw self::generateException($message, "id", "cannot be " . AuthorizationInterface::ANY);
        }
    }

    public static function ensureValidIndividualResourceIds(?string $message, ?string $ids): void
    {
        self::ensureNotNull($message, "id", $ids);
        foreach ($ids as $id) {
            self::ensureValidIndividualResourceId($message, $id);
        }
    }

    public static function ensureWhitelistedResourceId(CommandContext $commandContext, ?string $resourceType, ?string $resourceId): void
    {
        $resourcePattern = self::determineResourceWhitelistPattern($commandContext->getProcessEngineConfiguration(), $resourceType);

        if (!preg_match($resourcePattern, $resourceId)) {
            throw self::generateException($resourceType . " has an invalid id", "'" . $resourceId . "'", "is not a valid resource identifier.");
        }
    }

    public static function ensureTrue(?string $message, bool $value): void
    {
        if (!$value) {
            throw new ProcessEngineException($message);
        }
    }

    public static function ensureFalse(?string $message, bool $value): void
    {
        self::ensureTrue($message, !$value);
    }

    public static function ensureActiveCommandContext(?string $operation): void
    {
        if (Context::getCommandContext() == null) {
            //throw LOG.notInsideCommandContext(operation);
            throw new \Exception("notInsideCommandContext");
        }
    }

    protected static function determineResourceWhitelistPattern(ProcessEngineConfiguration $processEngineConfiguration, ?string $resourceType): ?string
    {
        $resourcePattern = null;

        if ($resourceType == "User") {
            $resourcePattern = $processEngineConfiguration->getUserResourceWhitelistPattern();
        }

        if ($resourceType == "Group") {
            $resourcePattern =  $processEngineConfiguration->getGroupResourceWhitelistPattern();
        }

        if ($resourceType == "Tenant") {
            $resourcePattern = $processEngineConfiguration->getTenantResourceWhitelistPattern();
        }

        if (!empty($resourcePattern)) {
            return $resourcePattern;
        }

        return $processEngineConfiguration->getGeneralResourceWhitelistPattern();
    }

    protected static function generateException(?string $message, ?string $variableName = null, ?string $description = null): \Exception
    {
        $formattedMessage = self::formatMessage($message, $variableName, $description);
        return new \Exception($formattedMessage);
    }

    protected static function formatMessage(?string $message, ?string $variableName = null, ?string $description = null): ?string
    {
        if ($variableName !== null && $description !== null) {
            return sprintf("%s: %s %s", $message, $variableName, $description);
        }
        return $message;
    }
}
