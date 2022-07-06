<?php

namespace Jabe\Engine\Impl\Bpmn\Helper;

use Jabe\Engine\Impl\Bpmn\Parser\EscalationEventDefinition;
use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface
};
use Jabe\Engine\Impl\Tree\TreeVisitorInterface;

class EscalationEventDefinitionFinder implements TreeVisitorInterface
{
    protected $escalationEventDefinition;

    protected $escalationCode;
    protected $throwEscalationActivity;

    public function __construct(string $escalationCode, PvmActivityInterface $throwEscalationActivity)
    {
        $this->escalationCode = $escalationCode;
        $this->throwEscalationActivity = $throwEscalationActivity;
    }

    public function visit($scope): void
    {
        $escalationEventDefinitions = $scope->getProperties()->get(BpmnProperties::escalationEventDefinitions());
        $this->escalationEventDefinition = $this->findMatchingEscalationEventDefinition($escalationEventDefinitions);
    }

    protected function findMatchingEscalationEventDefinition(array $escalationEventDefinitions): ?EscalationEventDefinition
    {
        foreach ($escalationEventDefinitions as $escalationEventDefinition) {
            if ($this->isMatchingEscalationCode($escalationEventDefinition) && !$this->isReThrowingEscalationEventSubprocess($escalationEventDefinition)) {
                return $escalationEventDefinition;
            }
        }
        return null;
    }

    protected function isMatchingEscalationCode(EscalationEventDefinition $escalationEventDefinition): bool
    {
        $escalationCode = $escalationEventDefinition->getEscalationCode();
        return $escalationCode === null || $escalationCode == $this->escalationCode;
    }

    protected function isReThrowingEscalationEventSubprocess(EscalationEventDefinition $escalationEventDefinition): bool
    {
        $escalationHandler = $escalationEventDefinition->getEscalationHandler();
        return $escalationHandler->isSubProcessScope() && $escalationHandler == $throwEscalationActivity->getFlowScope();
    }

    public function getEscalationEventDefinition(): EscalationEventDefinition
    {
        return $this->escalationEventDefinition;
    }
}
