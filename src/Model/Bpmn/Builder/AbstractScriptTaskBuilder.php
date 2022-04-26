<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
    ScriptInterface,
    ScriptTaskInterface
};

abstract class AbstractScriptTaskBuilder extends AbstractTaskBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ScriptTaskInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function scriptFormat(string $scriptFormat): AbstractScriptTaskBuilder
    {
        $this->element->setScriptFormat($scriptFormat);
        return $this;
    }

    public function script(ScriptInterface $script): AbstractScriptTaskBuilder
    {
        $this->element->setScript($script);
        return $this;
    }

    public function scriptText(string $scriptText): AbstractScriptTaskBuilder
    {
        $script = $this->createChild(null, ScriptInterface::class);
        $script->setTextContent($scriptText);
        return $this;
    }

    public function resultVariable(string $resultVariable): AbstractScriptTaskBuilder
    {
        $this->element->setResultVariable($resultVariable);
        return $this;
    }

    public function resource(string $resource): AbstractScriptTaskBuilder
    {
        $this->element->setResource($resource);
        return $this;
    }
}
