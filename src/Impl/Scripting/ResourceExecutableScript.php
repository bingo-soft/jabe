<?php

namespace Jabe\Impl\Scripting;

use Script\{
    BindingsInterface,
    ScriptEngineInterface
};
use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Util\ResourceUtil;

class ResourceExecutableScript extends SourceExecutableScript
{
    protected $scriptResource;

    public function __construct(?string $language, ?string $scriptResource)
    {
        parent::__construct($language, null);
        $this->scriptResource = $scriptResource;
    }

    public function evaluate(ScriptEngineInterface $engine, VariableScopeInterface $variableScope, BindingsInterface $bindings)
    {
        if ($this->scriptSource === null) {
            $this->loadScriptSource();
        }
        return parent::evaluate($engine, $variableScope, $bindings);
    }

    protected function loadScriptSource(): void
    {
        if ($this->getScriptSource() === null) {
            $deployment = Context::getCoreExecutionContext()->getDeployment();
            $source = ResourceUtil::loadResourceContent($this->scriptResource, $deployment);
            $this->setScriptSource($source);
        }
    }
}
