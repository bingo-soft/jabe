<?php

namespace BpmPlatform\Engine\Impl\Cfg;

use BpmPlatform\Engine\Impl\Bpmn\Parser\{
    BpmnParse,
    BpmnParser
};

interface BpmnParseFactoryInterface
{
    public function createBpmnParse(BpmnParser $bpmnParser): BpmnParse;
}
