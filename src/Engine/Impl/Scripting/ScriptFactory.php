<?php

namespace BpmPlatform\Engine\Impl\Scripting;

use BpmPlatform\Engine\Delegate\ExpressionInterface;

class ScriptFactory
{
    public function createScriptFromResource(string $language, $resource): ExecutableScript
    {
        if ($resource instanceof ExpressionInterface) {
            return new DynamicResourceExecutableScript($language, $resource);
        }
        return new ResourceExecutableScript($language, $resource);
    }

    public function createScriptFromSource(string $language, $source): ExecutableScript
    {
        if ($source instanceof ExpressionInterface) {
            return new DynamicSourceExecutableScript($language, $source);
        }
        return new SourceExecutableScript($language, $source);
    }
}
