<?php

namespace Jabe\Impl\Util;

use Jabe\BadUserRequestException;
use Jabe\Impl\ProcessEngineException;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;

class QueryMaxResultsLimitUtil
{
    public static function checkMaxResultsLimit(int $resultsCount, int $maxResultsLimit = null, bool $isUserAuthenticated = null): void
    {
        if ($maxResultsLimit === null && $isUserAuthenticated === null) {
            $processEngineConfiguration = Context::getProcessEngineConfiguration();
            if ($processEngineConfiguration === null) {
                throw new ProcessEngineException("Command context unset.");
            }

            self::checkMaxResultsLimit(
                $resultsCount,
                self::getMaxResultsLimit($processEngineConfiguration),
                self::isUserAuthenticated($processEngineConfiguration)
            );
        } else {
            if ($isUserAuthenticated && $maxResultsLimit < PHP_INT_MAX) {
                if ($resultsCount == PHP_INT_MAX) {
                    throw new BadUserRequestException("An unbound number of results is forbidden!");
                } elseif ($resultsCount > $maxResultsLimit) {
                    throw new BadUserRequestException("Max results limit of " . $maxResultsLimit . " exceeded!");
                }
            }
        }
    }

    protected static function isUserAuthenticated(ProcessEngineConfigurationImpl $processEngineConfig): bool
    {
        $userId = self::getAuthenticatedUserId($processEngineConfig);
        return $userId != null && !empty($userId);
    }

    protected static function getAuthenticatedUserId(ProcessEngineConfigurationImpl $processEngineConfig): ?string
    {
        $identityService = $processEngineConfig->getIdentityService();
        $currentAuthentication = $identityService->getCurrentAuthentication();
        if ($currentAuthentication === null) {
            return null;
        } else {
            return $currentAuthentication->getUserId();
        }
    }
}
