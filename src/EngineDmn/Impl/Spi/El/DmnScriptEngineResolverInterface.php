<?php

namespace Jabe\EngineDmn\Impl\Spi\El;

use Jabe\Engine\Impl\Util\Scripting\ScriptEngineInterface;

interface DmnScriptEngineResolverInterface
{
    public function getScriptEngineForLanguage(string $language): ScriptEngineInterface;
}
