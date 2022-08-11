<?php

namespace Jabe\Engine\Application\Impl;

use Jabe\Engine\Application\AbstractProcessApplication;
use El\ELResolver;

class DefaultElResolverLookup
{
    public static function lookupResolver(AbstractProcessApplication $processApplication): ?ELResolver
    {
        return null;
    }
}
