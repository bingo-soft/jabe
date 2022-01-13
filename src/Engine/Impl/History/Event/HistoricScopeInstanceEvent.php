<?php

namespace BpmPlatform\Engine\Impl\History\Event;

class HistoricScopeInstanceEvent extends HistoryEvent
{
    protected $durationInMillis;
    protected $startTime;
    protected $endTime;

    // getters / setters ////////////////////////////////////

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getDurationInMillis(): ?int
    {
        if ($this->durationInMillis != null) {
            return $this->durationInMillis;
        } elseif ($this->startTime != null && $this->endTime != null) {
            //@TODO. Probably should multiply by 1000?
            return intval($this->endTime) - intval($this->startTime);
        } else {
            return null;
        }
    }

    public function setDurationInMillis(int $durationInMillis): void
    {
        $this->durationInMillis = $durationInMillis;
    }

    public function getDurationRaw(): int
    {
        return $this->durationInMillis;
    }
}
