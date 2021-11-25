<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Util\El\FunctionMapper;
use BpmPlatform\Engine\Impl\Util\ReflectUtil;

class CommandContextFunctionMapper extends FunctionMapper
{

    public static $COMMAND_CONTEXT_FUNCTION_MAP;

    public function resolveFunction(string $prefix, string $localName): ?\ReflectionMethod
    {
        // Context functions are used un-prefixed
        $this->ensureContextFunctionMapInitialized();
        if (is_array(self::$COMMAND_CONTEXT_FUNCTION_MAP) && array_key_exists($localName, self::$COMMAND_CONTEXT_FUNCTION_MAP)) {
            return self::$COMMAND_CONTEXT_FUNCTION_MAP[$localName];
        }
        return null;
    }

    protected function ensureContextFunctionMapInitialized(): void
    {
        if (self::$COMMAND_CONTEXT_FUNCTION_MAP == null) {
            self::$COMMAND_CONTEXT_FUNCTION_MAP = [];
            $this->createMethodBindings();
        }
    }

    protected function createMethodBindings(): void
    {
        $mapperClass = get_class($this);
        self::$COMMAND_CONTEXT_FUNCTION_MAP["currentUser"] = ReflectUtil::getMethod($mapperClass, "currentUser");
        self::$COMMAND_CONTEXT_FUNCTION_MAP["currentUserGroups"] = ReflectUtil::getMethod($mapperClass, "currentUserGroups");
    }

    public static function currentUser(): ?string
    {
        $commandContext = Context::getCommandContext();
        if ($commandContext != null) {
            return $commandContext->getAuthenticatedUserId();
        } else {
            return null;
        }
    }

    public static function currentUserGroups(): array
    {
        $commandContext = Context::getCommandContext();
        if ($commandContext != null) {
            return $commandContext->getAuthenticatedGroupIds();
        } else {
            return null;
        }
    }
}
