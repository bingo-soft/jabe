<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;

class BpmnStackTrace
{
    //private final static ContextLogger LOG = ProcessEngineLogger.CONTEXT_LOGGER;

    protected $perfromedInvocations = [];

    public function printStackTrace(bool $verbose): void
    {
        if (empty($this->perfromedInvocations)) {
            return;
        }

        $writer = "";
        $writer .= "BPMN Stack Trace:\n";

        if (!$verbose) {
            $this->logNonVerbose($writer);
        } else {
            $this->logVerbose($writer);
        }

        //LOG.bpmnStackTrace(writer.toString());

        $this->perfromedInvocations = [];
    }

    protected function logNonVerbose(string &$writer): void
    {
        // log the failed operation verbosely
        if (count($this->perfromedInvocations)) {
            $this->writeInvocation($this->perfromedInvocations[count($this->perfromedInvocations) - 1], $writer);
        }

        // log human consumable trace of activity ids and names
        $activityTrace = $this->collectActivityTrace();
        $this->logActivityTrace($writer, $activityTrace);
    }

    protected function logVerbose(string &$writer): void
    {
        // log process engine developer consumable trace
        $perfromedInvocations = array_reverse($this->perfromedInvocations);
        foreach ($perfromedInvocations as $invocation) {
            $this->writeInvocation($invocation, $writer);
        }
    }

    protected function logActivityTrace(string &$writer, array $activities): void
    {
        for ($i = 0; $i < count($activities); $i += 1) {
            if ($i != 0) {
                $writer .= "\t  ^\n";
                $writer .= "\t  |\n";
            }
            $writer .= "\t";

            $activity = $activities[$i];
            $activityId = $activity->get("activityId");
            $writer .= $activityId;

            $activityName = $activity->get("activityName");
            if ($activityName != null) {
                $writer .= ", name=";
                $writer .= $activityName;
            }

            $writer .= "\n";
        }
    }

    protected function collectActivityTrace(): array
    {
        $activityTrace = [];
        foreach ($this->perfromedInvocations as $atomicOperationInvocation) {
            $activityId = $atomicOperationInvocation->getActivityId();
            if ($activityId == null) {
                continue;
            }

            $activity = [];
            $activity["activityId"] = $activityId;

            $activityName = $atomicOperationInvocation->getActivityName();
            if ($activityName != null) {
                $activity["activityName"] = $activityName;
            }

            if (
                empty($activityTrace) ||
                $activity->get("activityId") != $activityTrace[0]->get("activityId")
            ) {
                array_unshift($activityTrace, $activity);
            }
        }
        return $activityTrace;
    }

    public function add(AtomicOperationInvocation $atomicOperationInvocation): void
    {
        $this->perfromedInvocations[] = $atomicOperationInvocation;
    }

    protected function writeInvocation(AtomicOperationInvocation $invocation, string &$writer): void
    {
        $writer .= "\t";
        $writer .= $invocation->getActivityId();
        $writer .= " (";
        $writer .= $invocation->getOperation()->getCanonicalName();
        $writer .= ", ";
        $writer .= $invocation->getExecution()->__toString();

        if ($invocation->isPerformAsync()) {
            $writer .= ", ASYNC";
        }

        if ($invocation->getApplicationContextName() != null) {
            $writer .= ", pa=";
            $writer .= $invocation->getApplicationContextName();
        }

        $writer .= ")\n";
    }
}
