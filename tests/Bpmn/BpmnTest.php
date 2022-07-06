<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use Jabe\Model\Bpmn\Bpmn;

class BpmnTest extends TestCase
{
    public function testBpmn(): void
    {
        $this->assertFalse(Bpmn::getInstance() === null);
    }
}
