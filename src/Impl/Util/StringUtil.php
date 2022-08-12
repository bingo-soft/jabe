<?php

namespace Jabe\Impl\Util;

use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\El\ExpressionManager;

class StringUtil
{
    /**
     * Note: String#length() counts Unicode supplementary
     * characters twice, so for a String consisting only of those,
     * the limit is effectively MAX_LONG_STRING_LENGTH / 2
     */
    public const DB_MAX_STRING_LENGTH = 666;

    /**
     * Checks whether a String seams to be an expression or not
     *
     * Note: In most cases you should check for composite expressions. See
     * {@link #isCompositeExpression(String, ExpressionManager)} for more information.
     *
     * @param text the text to check
     * @return true if the text seams to be an expression false otherwise
     */
    public static function isExpression(string $text): bool
    {
        $text = trim($text);
        return strpos($text, '${') === 0 || strpos($text, '#{') === 0;
    }

    /**
     * Checks whether a String seams to be a composite expression or not. In contrast to an eval expression
     * is the composite expression also allowed to consist of a combination of literal and eval expressions, e.g.,
     * "Welcome ${customer.name} to our site".
     *
     * Note: If you just want to allow eval expression, then the expression must always start with "#{" or "${".
     * Use {@link #isExpression(String)} to conduct these kind of checks.
     *
     */
    public static function isCompositeExpression(string $text, ExpressionManager $expressionManager): bool
    {
        return !$expressionManager->createExpression($text)->isLiteralText();
    }

    public static function split(string $text, string $delimiter): array
    {
        return explode($delimiter, $text);
    }

    public static function hasAnySuffix(string $text, array $suffixes): bool
    {
        foreach ($suffixes as $suffix) {
            if (str_ends_with($text, $suffix)) {
                return true;
            }
        }

        return false;
    }

    public static function fromBytes(string $bytes): string
    {
        return $bytes;
    }

    public static function toByteArray(string $string): string
    {
        return $string;
    }

    /**
     * Trims the input to the {@link #DB_MAX_STRING_LENGTH maxium length allowed} for persistence with our default database schema
     *
     * @param string the input that might be trimmed if maximum length is exceeded
     * @return the input, eventually trimmed to {@link #DB_MAX_STRING_LENGTH}
     */
    public static function trimToMaximumLengthAllowed(?string $string): string
    {
        if ($string !== null && strlen($string) > self::DB_MAX_STRING_LENGTH) {
            return substr($string, 0, self::DB_MAX_STRING_LENGTH);
        }
        return string;
    }

    public static function joinDbEntityIds(array $dbEntities): string
    {
        return self::join(array_map(function ($entity) {
            return $entity->getId();
        }, $dbEntities));
    }

    public static function joinProcessElementInstanceIds(array $processElementInstances): string
    {
        return self::join(array_map(function ($element) {
            return $element->getId();
        }, $processElementInstances));
    }

    /**
     * @param string the String to check.
     * @return a boolean <code>TRUE</code> if the String is not null and not empty. <code>FALSE</code> otherwise.
     */
    public static function hasText(?string $string): bool
    {
        return !empty($string);
    }

    public static function join(array $data): string
    {
        return implode(", ", $data);
    }
}
