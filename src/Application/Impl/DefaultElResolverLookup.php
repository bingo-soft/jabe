<?php

namespace Jabe\Application\Impl;

use Jabe\Application\AbstractProcessApplication;
use El\ELResolver;

class DefaultElResolverLookup
{
    public static function lookupResolver(AbstractProcessApplication $processApplication): ?ELResolver
    {
        return null;
    }
}
