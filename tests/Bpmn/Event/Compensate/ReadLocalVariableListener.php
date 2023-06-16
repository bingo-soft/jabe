<?php

namespace Tests\Bpmn\Event\Compensate;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};

class ReadLocalVariableListener implements ExecutionListenerInterface
{
    protected $variableEvents = [];
    protected $variableName;

    public function __construct(string $variableName)
    {
        $this->variableName = $variableName;
    }

    public function __serialize(): array
    {
        $variableEvents = [];
        foreach ($this->variableEvents as $event) {
            $serialized = serialize($event);
            preg_match_all("/([C|O]+:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $serialized, $matches);
            if (!empty($matches[2])) {
                foreach ($matches[2] as $className) {
                    $serialized = str_replace($className, str_replace('\\', '.', $className), $serialized);
                }
            }
            $variableEvents[] = serialize($serialized);
        }
        return [
            'variableName' => $this->variableName,
            'variableEvents' => $variableEvents
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->variableName = $data['variableName'];
        $variableEvents = [];
        foreach ($data['variableEvents'] as $event) {
            $eventStr = unserialize($event);
            preg_match_all("/([C|O]+:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $eventStr, $matches);
            if (!empty($matches[2])) {
                foreach ($matches[2] as $className) {
                    $eventStr = str_replace($className, str_replace('.', '\\', $className), $eventStr);
                }
            }
            $variableEvents[] = unserialize($eventStr);
        }
        $this->variableEvents = $variableEvents;
    }

    public function getVariableEvents(): array
    {
        return $this->variableEvents;
    }

    public function setVariableEvents(array $variableEvents): void
    {
        $this->variableEvents = $variableEvents;
    }

    public function notify($execution): void
    {
        if (!$execution->hasVariableLocal($this->variableName)) {
            return;
        }

        $value = $execution->getVariableLocal($this->variableName);

        $event = new VariableEvent();
        $event->variableName = $this->variableName;
        $event->variableValue = $value;
        $event->eventName = $execution->getEventName();
        $event->activityInstanceId = $execution->getActivityInstanceId();

        $this->variableEvents[] = $event;
    }
}
