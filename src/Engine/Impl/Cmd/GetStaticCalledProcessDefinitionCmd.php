<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\AuthorizationException;
use BpmPlatform\Engine\Impl\Bpmn\Behavior\CallActivityBehavior;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use BpmPlatform\Engine\Impl\Repository\CalledProcessDefinitionImpl;
use BpmPlatform\Engine\Impl\Util\CallableElementUtil;

class GetStaticCalledProcessDefinitionCmd implements CommandInterface
{
    protected $processDefinitionId;

    public function __construct(string $processDefinitionId)
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    protected function findCallActivitiesInProcess(ProcessDefinitionEntity $processDefinition): array
    {
        $callActivities = [];

        $toCheck = $processDefinition->getActivities();
        while (!empty($toCheck)) {
            $candidate = array_shift($toCheck);

            if (!empty($candidate->getActivities())) {
                $toCheck = array_merge($toCheck, $candidate->getActivities());
            }
            if ($candidate->getActivityBehavior() instanceof CallActivityBehavior) {
                $callActivities[] = $candidate;
            }
        }
        return $callActivities;
    }

    public function execute(CommandContext $commandContext)
    {
        $processDefinition = (new GetDeployedProcessDefinitionCmd($processDefinitionId, true))->execute($commandContext);

        $callActivities = $this->findCallActivitiesInProcess($processDefinition);

        $calledProcessDefinitionsById = [];

        foreach ($callActivities as $activity) {
            $behavior = $activity->getActivityBehavior();
            $callableElement = $behavior->getCallableElement();
            $activityId = $activity->getActivityId();

            $tenantId = $processDefinition->getTenantId();
            $calledProcess = CallableElementUtil::getStaticallyBoundProcessDefinition(
                $this->processDefinitionId,
                $activityId,
                $callableElement,
                $tenantId
            );

            if ($calledProcess != null) {
                if (!array_key_exists($calledProcess->getId(), $calledProcessDefinitionsById)) {
                    try {
                        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                            $checker->checkReadProcessDefinition($calledProcess);
                        }
                        $result = new CalledProcessDefinitionImpl($calledProcess, $this->processDefinitionId);
                        $result->addCallingCallActivity($activityId);
                        $calledProcessDefinitionsById[$calledProcess->getId()] = $result;
                    } catch (AuthorizationException $e) {
                        // unauthorized Process definitions will not be added.
                        //CMD_LOGGER.debugNotAllowedToResolveCalledProcess(calledProcess.getId(), processDefinitionId, activityId, e);
                    }
                } else {
                    $calledProcessDefinitionsById[$calledProcess->getId()]->addCallingCallActivity($activityId);
                }
            }
        }
        return array_values($calledProcessDefinitionsById);
    }
}
