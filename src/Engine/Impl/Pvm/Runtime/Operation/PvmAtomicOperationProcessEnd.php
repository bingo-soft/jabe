<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmLogger
};
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationProcessEnd extends PvmAtomicOperationActivityInstanceEnd
{
    //private final static PvmLogger LOG = PvmLogger.PVM_LOGGER;

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getProcessDefinition();
    }

    protected function getEventName(): string
    {
        return ExecutionListenerInterface::EVENTNAME_END;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        $execution->leaveActivityInstance();

        $superExecution = $execution->getSuperExecution();
        //CmmnActivityExecution superCaseExecution = $execution->getSuperCaseExecution();

        $subProcessActivityBehavior = null;
        $transferVariablesBehavior = null;

        // copy variables before destroying the ended sub process instance
        if ($superExecution !== null) {
            $activity = $superExecution->getActivity();
            $subProcessActivityBehavior = $activity->getActivityBehavior();
            try {
                $subProcessActivityBehavior->passOutputVariables($superExecution, $execution);
            } catch (\Exception $e) {
                //LOG.exceptionWhileCompletingSupProcess(execution, e);
                throw new ProcessEngineException("Error while completing sub process of execution " . $execution, $e);
            }
        } /*else if (superCaseExecution !== null) {
                CmmnActivity activity = superCaseExecution.getActivity();
                transferVariablesBehavior = (TransferVariablesActivityBehavior) activity.getActivityBehavior();
            try {
                transferVariablesBehavior.transferVariables(execution, superCaseExecution);
            } catch (RuntimeException e) {
                LOG.exceptionWhileCompletingSupProcess(execution, e);
                throw e;
            } catch (Exception e) {
                LOG.exceptionWhileCompletingSupProcess(execution, e);
                throw new ProcessEngineException("Error while completing sub process of execution " + execution, e);
            }
        }*/

        $execution->destroy();
        $execution->remove();

        // and trigger execution afterwards
        if ($superExecution !== null) {
            $superExecution->setSubProcessInstance(null);
            try {
                $subProcessActivityBehavior->completed($superExecution);
            } catch (\Exception $e) {
                //LOG.exceptionWhileCompletingSupProcess(execution, e);
                throw new ProcessEngineException("Error while completing sub process of execution " . $execution, $e);
            }
        }/* elseif ($superCaseExecution !== null) {
            $superCaseExecution->complete();
        }*/
    }

    public function getCanonicalName(): string
    {
        return "process-end";
    }
}
