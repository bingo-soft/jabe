<?php

namespace Jabe\Impl\Util;

use Jabe\Exception\NotValidException;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Parser\FailedJobRetryConfiguration;
use Jabe\Impl\Calendar\DurationHelper;
use Jabe\Impl\Context\Context;
use Jabe\Impl\El\{
    ExpressionInterface,
    ExpressionManager
};

class ParseUtil
{
    //private static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;

    /**
     * Parse History Time To Live in ISO-8601 format to integer and set into the given entity
     * @param historyTimeToLive
     */
    public static function parseHistoryTimeToLive(?string $historyTimeToLive): ?int
    {
        $timeToLive = null;

        if (!empty($historyTimeToLive)) {
            preg_match_all('/^P(\d+)D$/', $historyTimeToLive, $matches);
            if (!empty($matches[0])) {
                $historyTimeToLive = $matches[0];
            }
            $timeToLive = intval($historyTimeToLive);
        }

        if ($timeToLive !== null && $timeToLive < 0) {
            throw new NotValidException("Cannot parse historyTimeToLive: negative value is not allowed");
        }

        return $timeToLive;
    }

    public static function parseRetryIntervals(?string $retryIntervals): ?FailedJobRetryConfiguration
    {
        if (!empty($retryIntervals)) {
            if (StringUtil::isExpression($retryIntervals)) {
                $expressionManager = Context::getProcessEngineConfiguration()->getExpressionManager();
                $expression = $expressionManager->createExpression($retryIntervals);
                return new FailedJobRetryConfiguration($expression);
            }

            $intervals = StringUtil::split($retryIntervals, ",");
            $retries = count($intervals) + 1;

            if (count($intervals) == 1) {
                try {
                    $durationHelper = new DurationHelper($intervals[0]);

                    if ($durationHelper->isRepeat()) {
                        $retries = $durationHelper->getTimes();
                    }
                } catch (\Exception $e) {
                    //LOG.logParsingRetryIntervals(intervals[0], e);
                    return null;
                }
            }
            return new FailedJobRetryConfiguration($retries, $intervals);
        } else {
            return null;
        }
    }

    public static function parseProcessEngineVersion($trimSuffixEEOrVersion, bool $trimSuffixEE = null): ProcessEngineDetails
    {
        if (is_bool($trimSuffixEEOrVersion)) {
            $version = ProductPropertiesUtil::getProductVersion();
            return self::parseProcessEngineVersion($trimSuffixEEOrVersion, $trimSuffixEE);
        } else {
            $edition = ProcessEngineDetails::EDITION_COMMUNITY;
            $version = $trimSuffixEEOrVersion;
            if (strpos($trimSuffixEEOrVersion, "-ee") !== false) {
                $edition = ProcessEngineDetails::EDITION_ENTERPRISE;
                if ($trimSuffixEE) {
                    $version = str_replace("-ee", "", $trimSuffixEEOrVersion); // trim `-ee` suffix
                }
            }

            return new ProcessEngineDetails($version, $edition);
        }
    }

    public static function parseServerVendor(string $applicationServerInfo): string
    {
        $serverVendor = "";
        preg_match_all('/([\sa-zA-Z]+)/', $applicationServerInfo, $matches);
        if (!empty($matches[0])) {
            $serverVendor = trim($matches[0]);
        }

        return $serverVendor;
    }
}
