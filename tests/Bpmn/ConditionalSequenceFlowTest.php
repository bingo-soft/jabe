<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use Jabe\Model\Bpmn\Bpmn;

class ConditionalSequenceFlowTest extends TestCase
{
    protected $modelInstance;
    protected $flow1;
    protected $flow2;
    protected $flow3;
    protected $conditionExpression1;
    protected $conditionExpression2;
    protected $conditionExpression3;

    protected function setUp(): void
    {
        $stream = fopen('tests/Bpmn/Resources/ConditionalSequenceFlowTest.xml', 'r+');
        $this->modelInstance = Bpmn::getInstance()->readModelFromStream($stream);
        $this->flow1 = $this->modelInstance->getModelElementById('flow1');
        $this->flow2 = $this->modelInstance->getModelElementById('flow2');
        $this->flow3 = $this->modelInstance->getModelElementById('flow3');
        $this->conditionExpression1 = $this->flow1->getConditionExpression();
        $this->conditionExpression2 = $this->flow2->getConditionExpression();
        $this->conditionExpression3 = $this->flow3->getConditionExpression();
    }

    protected function tearDown(): void
    {
        Bpmn::getInstance()->validateModel($this->modelInstance);
    }

    public function testShouldHaveTypeTFormalExpression(): void
    {
        $this->assertEquals('tFormalExpression', $this->conditionExpression1->getType());
        $this->assertEquals('tFormalExpression', $this->conditionExpression2->getType());
        $this->assertEquals('tFormalExpression', $this->conditionExpression3->getType());
    }

    public function testShouldHaveLanguage(): void
    {
        $this->assertNull($this->conditionExpression1->getLanguage());
        $this->assertNull($this->conditionExpression2->getLanguage());
        $this->assertEquals('groovy', $this->conditionExpression3->getLanguage());
    }

    public function testShouldHaveSourceCode(): void
    {
        $this->assertEquals('test', $this->conditionExpression1->getTextContent());
        $this->assertEquals('${test}', $this->conditionExpression2->getTextContent());
        $this->assertEmpty($this->conditionExpression3->getTextContent());
    }

    public function testShouldHaveResource(): void
    {
        $this->assertNull($this->conditionExpression1->getResource());
        $this->assertNull($this->conditionExpression2->getResource());
        $this->assertEquals('test.groovy', $this->conditionExpression3->getResource());
    }
}
