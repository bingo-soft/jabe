<?php

namespace Jabe\Engine\Impl\Cfg;

use Jabe\Engine\Impl\Bpmn\Parser\{
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
