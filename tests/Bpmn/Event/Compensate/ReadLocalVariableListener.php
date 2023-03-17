<?php

namespace Tests\Bpmn\Event\Compensate;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};

class ReadLocalVariableListener implements ExecutionListenerInterface, \Serializable
{
    protected $variableEvents = [];
    protected $variableName;

    public function __construct(string $variableName)
    {
        $this->variableName = $variableName;
    }

    public function serialize()
    {
        $variableEvents = [];
        foreach ($this->variableEvents as $event) {
            $serialized = serialize($event);
            preg_match_all("/(C:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $serialized, $matches);
            if (!empty($matches[2])) {
                foreach ($matches[2] as $className) {
                    $serialized = str_replace($className, str_replace('\\', '.', $className), $serialized);
                }
            }
            $variableEvents[] = serialize($serialized);
        }
        return json_encode([
            'variableName' => $this->variableName,
            'variableEvents' => $variableEvents
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->variableName = $json->variableName;
        $variableEvents = [];
        foreach ($json->variableEvents as $event) {
            $eventStr = unserialize($event);
            preg_match_all("/(C:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $eventStr, $matches);
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
