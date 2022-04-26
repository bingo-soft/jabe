<?php

namespace Jabe\Engine\Delegate;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instnace\FlowElementInterface;

interface BpmnModelExecutionContextInterface
{
    public function getBpmnModelInstance(): BpmnModelInstanceInterface;

    /**
     * <p>Returns the currently executed Element in the BPMN Model. This method returns a {@link FlowElement} which may be casted
     * to the concrete type of the Bpmn Model Element currently executed.</p>
     *
     * <p>If called from a Service {@link ExecutionListener}, the method will return the corresponding {@link FlowNode}
     * for {@link ExecutionListener#EVENTNAME_START} and {@link ExecutionListener#EVENTNAME_END} and the corresponding
     * {@link SequenceFlow} for {@link ExecutionListener#EVENTNAME_TAKE}.</p>
     *
     * @return the {@link FlowElement} corresponding to the current Bpmn Model Element
     */
    public function getBpmnModelElementInstance(): FlowElementInterface;
}
