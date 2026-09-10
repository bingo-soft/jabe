<?php

namespace Tests\Impl\Bpmn\Helper;

use Jabe\Impl\Bpmn\Helper\BpmnExceptionHandler;
use PHPUnit\Framework\TestCase;

class BpmnExceptionHandlerTest extends TestCase
{
    public function testPropagateExceptionAcceptsPhpError(): void
    {
        $reflection = new \ReflectionMethod(BpmnExceptionHandler::class, 'checkIfCauseOfExceptionIsBpmnError');
        $error = new \Error('worker failure');

        $this->assertNull($reflection->invoke(null, $error));
    }
}
