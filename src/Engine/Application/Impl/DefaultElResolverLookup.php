<?php

namespace BpmPlatform\Engine\Application\Impl;

use BpmPlatform\Engine\Application\AbstractProcessApplication;
use BpmPlatform\Engine\Impl\Util\El\ELResolver;

class DefaultElResolverLookup
{
    public static function lookupResolver(AbstractProcessApplication $processApplication): ?ELResolver
    {
        return null;
    }
}
