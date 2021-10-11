<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Bpmn\{
    Bpmn,
    BpmnModelInstanceInterface
};

abstract class BpmnModelTest extends TestCase
{
    protected $bpmnModelInstance;

    /**
     * @param string $test
     */
    protected function parseModel(string $test)
    {
        $stream = fopen('tests/Bpmn/Resources/' . $test . '.bpmn', 'r+');
        try {
            $this->bpmnModelInstance = Bpmn::getInstance()->readModelFromStream($stream);
        } finally {
            fclose($stream);
        }
    }

    public function getBpmnModel(): BpmnModelInstanceInterface
    {
        return $this->bpmnModelInstance;
    }
}
