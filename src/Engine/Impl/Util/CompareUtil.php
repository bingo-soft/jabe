<?php

namespace Jabe\Engine\Impl\Util;

class CompareUtil
{
    /**
     * Checks if any of the values are not in an ascending order. The check is done based on the Comparable#compareTo(Object) method.
     *
     * E.g. if we have {@code minPriority = 10}, {@code priority = 13} and {@code maxPriority = 5} and
     * {@code Integer[] values = {minPriority, priority, maxPriority}}. Then a call to {@link CompareUtil#areNotInAscendingOrder(Comparable[] values)}
     * will return {@code true}
     *
     * @param values to validate
     * @param <T> the type of the comparable
     * @return bool false if the not null values are in an ascending order or all the values are null, {@code true} otherwise
     */
    public static function areNotInAscendingOrder(...$values): bool
    {
        $lastNotNull = -1;
        for ($i = 0; $i < count($values); $i += 1) {
            $value = $values[$i];

            if ($value !== null) {
                if ($lastNotNull != -1 && $values[$lastNotNull] > $value) {
                    return true;
                }
                $lastNotNull = $i;
            }
        }
        return false;
    }

    /**
     * Checks if the element is not contained within the list of values. If the element, or the list are null then true is returned.
     *
     * @param element to check
     * @param values to check in
     * @param <T> the type of the element
     * @return bool true if the element and values are not {@code null} and the values does not contain the element, {@code false} otherwise
     */
    public static function elementIsNotContainedInList($element, array $values): bool
    {
        if ($element !== null && !empty($values)) {
            return !in_array($element, $values);
        } else {
            return false;
        }
    }

    /**
     * Checks if the element is contained within the list of values. If the element, or the list are null then true is returned.
     *
     * @param element to check
     * @param values to check in
     * @param <T> the type of the element
     * @return bool true if the element and values are not {@code null} and the values does not contain the element, {@code false} otherwise
     */
    public static function elementIsNotContainedInArray($element, ...$values): bool
    {
        if ($element !== null && !empty($values)) {
            return self::elementIsNotContainedInList($element, $values);
        } else {
            return false;
        }
    }

    /**
     * Checks if the element is contained within the list of values.
     *
     * @param element to check
     * @param values to check in
     * @param <T> the type of the element
     * @return bool true if the element and values are not {@code null} and the values contain the element,
     *   {@code false} otherwise
     */
    public static function elementIsContainedInList($element, array $values): bool
    {
        if ($element !== null && !empty($values)) {
            return in_array($element, $values);
        } else {
            return false;
        }
    }

    /**
     * Checks if the element is contained within the list of values.
     *
     * @param element to check
     * @param values to check in
     * @param <T> the type of the element
     * @return bool true if the element and values are not {@code null} and the values contain the element,
     *   {@code false} otherwise
     */
    public static function elementIsContainedInArray($element, ...$values): bool
    {
        if ($element !== null && !empty($values)) {
            return self::elementIsContainedInList($element, $values);
        } else {
            return false;
        }
    }

    /**
     * Returns any element if obj1.compareTo(obj2) == 0
     */
    public static function min($obj1, $obj2)
    {
        return ($obj1 <= $obj2) ? $obj1 : $obj2;
    }

    /**
     * Returns any element if obj1.compareTo(obj2) == 0
     */
    public static function max($obj1, $obj2)
    {
        return $obj1 >= $obj2 ? $obj1 : $obj2;
    }
}
