<?php

namespace BpmPlatform\EngineDmn\Impl\Spi\El;

use BpmPlatform\Engine\Impl\Util\Scripting\ScriptEngineInterface;

interface DmnScriptEngineResolverInterface
{
    public function getScriptEngineForLanguage(string $language): ScriptEngineInterface;
}
