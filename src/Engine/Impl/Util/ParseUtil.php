<?php

namespace Jabe\Engine\Impl\Util;

class ParseUtil
{
    public static function parseServerVendor(string $applicationServerInfo): string
    {
        $serverVendor = "";
        preg_match_all('([\sa-zA-Z]+)', $applicationServerInfo, $matches);
        if (!empty($matches[0])) {
            $serverVendor = trim($matches[0]);
        }

        return $serverVendor;
    }
}
