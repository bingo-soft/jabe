<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

class TimerJobConfiguration implements JobHandlerConfigurationInterface
{
    public const JOB_HANDLER_CONFIG_PROPERTY_DELIMITER = '$';
    public const JOB_HANDLER_CONFIG_PROPERTY_FOLLOW_UP_JOB_CREATED = "followUpJobCreated";
    public const JOB_HANDLER_CONFIG_TASK_LISTENER_PREFIX = "taskListener~";

    protected $timerElementKey;
    protected $timerElementSecondaryKey;
    protected $followUpJobCreated;

    public function getTimerElementKey(): string
    {
        return $this->timerElementKey;
    }

    public function setTimerElementKey(string $timerElementKey): void
    {
        $this->timerElementKey = $timerElementKey;
    }

    public function isFollowUpJobCreated(): bool
    {
        return $this->followUpJobCreated;
    }

    public function setFollowUpJobCreated(bool $followUpJobCreated): void
    {
        $this->followUpJobCreated = $followUpJobCreated;
    }

    public function getTimerElementSecondaryKey(): string
    {
        return $this->timerElementSecondaryKey;
    }

    public function setTimerElementSecondaryKey(string $timerElementSecondaryKey): void
    {
        $this->timerElementSecondaryKey = $timerElementSecondaryKey;
    }

    public function toCanonicalString(): string
    {
        $canonicalString = $this->timerElementKey;

        if ($this->timerElementSecondaryKey != null) {
            $canonicalString .= self::JOB_HANDLER_CONFIG_PROPERTY_DELIMITER . self::JOB_HANDLER_CONFIG_TASK_LISTENER_PREFIX . $this->timerElementSecondaryKey;
        }

        if ($this->followUpJobCreated) {
            $canonicalString .= self::JOB_HANDLER_CONFIG_PROPERTY_DELIMITER . self::JOB_HANDLER_CONFIG_PROPERTY_FOLLOW_UP_JOB_CREATED;
        }

        return $canonicalString;
    }
}
