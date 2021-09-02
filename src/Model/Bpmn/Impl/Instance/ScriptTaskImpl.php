<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Builder\ScriptTaskBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ScriptInterface,
    ScriptTaskInterface,
    TaskInterface
};

class ScriptTaskImpl extends TaskImpl implements ScriptTaskInterface
{
    protected static $scriptFormatAttribute;
    protected static $scriptChild;

    protected static $resultVariableAttribute;
    protected static $resourceAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ScriptTaskInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_SCRIPT_TASK
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(TaskInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ScriptTaskImpl($instanceContext);
                }
            }
        );

        self::$scriptFormatAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_SCRIPT_FORMAT)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$scriptChild = $sequenceBuilder->element(ScriptInterface::class)
        ->build();

        self::$resultVariableAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_RESULT_VARIABLE)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$resourceAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_RESOURCE)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): ScriptTaskBuilder
    {
        return new ScriptTaskBuilder($this->modelInstance, $this);
    }

    public function getScriptFormat(): string
    {
        return self::$scriptFormatAttribute->getValue($this);
    }

    public function setScriptFormat(string $scriptFormat): void
    {
        self::$scriptFormatAttribute->setValue($this, $scriptFormat);
    }

    public function getScript(): ScriptInterface
    {
        return self::$scriptChild->getChild($this);
    }

    public function setScript(ScriptInterface $script): void
    {
        self::$scriptChild->setChild($this, $script);
    }

    public function getResultVariable(): string
    {
        return self::$resultVariableAttribute->getValue($this);
    }

    public function setResultVariable(string $resultVariable): void
    {
        self::$resultVariableAttribute->setValue($this, $resultVariable);
    }

    public function getResource(): string
    {
        return self::$resourceAttribute->getValue($this);
    }

    public function setResource(string $resource): void
    {
        self::$resourceAttribute->setValue($this, $resource);
    }
}
