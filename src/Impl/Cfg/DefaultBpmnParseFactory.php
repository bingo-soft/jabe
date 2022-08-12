<?php

namespace Jabe\Impl\Cfg;

use Jabe\Impl\Bpmn\Parser\{
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
