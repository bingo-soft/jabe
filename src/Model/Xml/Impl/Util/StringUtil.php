<?php

namespace BpmPlatform\Model\Xml\Impl\Util;

class StringUtil
{
    public const PATTERN = '/(\w[^,]*)|([#$]\{[^}]*\})/mu';

    public static function splitCommaSeparatedList(?string $text): array
    {
        if (empty($text)) {
            return [];
        }
        preg_match_all(self::PATTERN, $text, $matches);
        $parts = [];
        foreach ($matches[0] as $match) {
            $parts[] = trim($match);
        }
        return $parts;
    }

    public static function joinCommaSeparatedList(?array $list): ?string
    {
        return self::joinList($list, ", ");
    }

    public static function splitListBySeparator(?string $text, string $separator): array
    {
        if (!empty($text)) {
            return explode($separator, $text);
        }
        return [];
    }

    public static function joinList(?array $list, string $separator): ?string
    {
        return $list == null ? null : implode($separator, $list);
    }
}
