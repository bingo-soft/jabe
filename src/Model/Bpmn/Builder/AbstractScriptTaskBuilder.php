<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ScriptInterface,
    ScriptTaskInterface
};

abstract class AbstractScriptTaskBuilder extends AbstractBaseElementBuilder
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
        return $this->myself;
    }

    public function script(ScriptInterface $script): AbstractScriptTaskBuilder
    {
        $this->element->setScript($script);
        return $this->myself;
    }

    public function scriptText(string $scriptText): AbstractScriptTaskBuilder
    {
        $script = $this->createChild(ScriptInterface::class);
        $script->setTextContent($scriptText);
        return $this->myself;
    }

    public function resultVariable(string $resultVariable): AbstractScriptTaskBuilder
    {
        $this->element->setResultVariable($resultVariable);
        return $this->myself;
    }

    public function resource(string $resource): AbstractScriptTaskBuilder
    {
        $this->element->setResource($resource);
        return $this->myself;
    }
}
