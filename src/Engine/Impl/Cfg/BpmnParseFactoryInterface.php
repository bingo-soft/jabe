<?php

namespace Jabe\Engine\Impl\Cfg;

use Jabe\Engine\Impl\Bpmn\Parser\{
    BpmnParse,
    BpmnParser
};

interface BpmnParseFactoryInterface
{
    public function createBpmnParse(BpmnParser $bpmnParser): BpmnParse;
}
