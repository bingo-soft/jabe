<?php

namespace BpmPlatform\Engine\Application\Impl;

use BpmPlatform\Engine\Application\AbstractProcessApplication;
use BpmPlatform\Engine\Impl\Expression\ELResolver;

class DefaultElResolverLookup
{
    public static function lookupResolver(AbstractProcessApplication $processApplication): ?ELResolver
    {
        return null;
    }
}
