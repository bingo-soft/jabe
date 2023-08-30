<?php

namespace Tests\Bpmn\ExecutionListener;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use Jabe\Impl\Core\Model\PropertyKey;
use Jabe\Impl\El\FixedValue;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;

class RecorderExecutionListener implements ExecutionListenerInterface
{
    private $parameter;

    private static $recordedEvents = [];

    public function __serialize(): array
    {
        $recordedEvents = [];
        foreach (self::$recordedEvents as $event) {
            $serialized = serialize($event);
            preg_match_all("/([C|O]+:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $serialized, $matches);
            if (!empty($matches[2])) {
                foreach ($matches[2] as $className) {
                    $serialized = str_replace($className, str_replace('\\', '.', $className), $serialized);
                }
            }
            $recordedEvents[] = serialize($serialized);
        }
        return [
            'recordedEvents' => $recordedEvents
        ];
    }

    public function __unserialize(array $data): void
    {
        $recordedEvents = [];
        foreach ($data['recordedEvents'] as $event) {
            $eventStr = unserialize($event);
            preg_match_all("/([C|O]+:\d+:\\\?\")(.*?)(?=\\\\\"|\")/", $eventStr, $matches);
            if (!empty($matches[2])) {
                foreach ($matches[2] as $className) {
                    $eventStr = str_replace($className, str_replace('.', '\\', $className), $eventStr);
                }
            }
            $recordedEvents[] = unserialize($eventStr);
        }
        self::$recordedEvents = $recordedEvents;
    }

    public function notify(/*DelegateExecutionInterface*/$execution): void
    {
        $parameterValue = null;
        if ($this->parameter != null) {
            $parameterValue = $this->parameter->getValue($execution);
        }

        $activityName = null;
        if ($execution->getActivity() != null) {
            $activityName = $execution->getActivity()->getProperties()->get(new PropertyKey("name"));
        }

        self::$recordedEvents[] = new RecordedEvent(
            $execution->getActivityId(),
            $activityName,
            $execution->getEventName(),
            $parameterValue,
            $execution->getActivityInstanceId(),
            $execution->getCurrentTransitionId(),
            $execution->isCanceled(),
            $execution->getId()
        );
        fwrite(STDERR, "Record event " . $execution->getEventName() . " on activity " . $execution->getActivityId() . "\n");
    }

    public static function clear(): void
    {
        self::$recordedEvents = [];
    }

    public static function &getRecordedEvents(): array
    {
        return self::$recordedEvents;
    }
}
