<?php

namespace Jabe\Delegate;

use Bpmn\BpmnModelInstanceInterface;
use Bpmn\Instance\FlowElementInterface;

interface BpmnModelExecutionContextInterface
{
    public function getBpmnModelInstance(): ?BpmnModelInstanceInterface;

    /**
     * <p>Returns the currently executed Element in the BPMN Model. This method returns a FlowElement which may be casted
     * to the concrete type of the Bpmn Model Element currently executed.</p>
     *
     * <p>If called from a Service ExecutionListener, the method will return the corresponding FlowNode
     * for ExecutionListener#EVENTNAME_START and ExecutionListener#EVENTNAME_END and the corresponding
     * SequenceFlow for ExecutionListener#EVENTNAME_TAKE.</p>
     *
     * @return FlowElementInterface the FlowElement corresponding to the current Bpmn Model Element
     */
    public function getBpmnModelElementInstance(): ?FlowElementInterface;
}
