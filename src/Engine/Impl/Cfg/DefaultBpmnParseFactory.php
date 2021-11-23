<?php

namespace BpmPlatform\Engine\Impl\Cfg;

use BpmPlatform\Engine\Impl\Bpmn\Parser\{
    BpmnParse,
    BpmnParser
};

class DefaultBpmnParseFactory implements BpmnParseFactoryInterface
{
    public function createBpmnParse(BpmnParser $bpmnParser): BpmnParse
    {
        return new BpmnParse($bpmnParser);
    }
}
