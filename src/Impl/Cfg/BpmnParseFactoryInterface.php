<?php

namespace Jabe\Impl\Cfg;

use Jabe\Impl\Bpmn\Parser\{
    BpmnParse,
    BpmnParser
};

interface BpmnParseFactoryInterface
{
    public function createBpmnParse(BpmnParser $bpmnParser): BpmnParse;
}
