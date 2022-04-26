<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AbstractTypeAssumption;
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Impl\QueryImpl;
use Jabe\Model\Bpmn\Instance\EventDefinitionInterface;
use Jabe\Model\Xml\Impl\Util\ReflectUtil;

abstract class AbstractEventDefinitionTest extends BpmnModelElementInstanceTest
{
    protected $eventDefinitionQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $inputStream = ReflectUtil::getResourceAsFile("tests/Bpmn/Resources/EventDefinitionsTest.xml");
        $event = Bpmn::getInstance()->readModelFromStream($inputStream)->getModelElementById("event");
        $this->eventDefinitionQuery = new QueryImpl($event->getEventDefinitions());
    }

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, EventDefinitionInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
