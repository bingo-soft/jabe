<?php

namespace BpmPlatform\Engine\Impl\History;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\Batch\BatchInterface;
use BpmPlatform\Engine\Batch\History\HistoricBatchQueryInterface;
use BpmPlatform\Engine\History\{
    SetRemovalTimeSelectModeForHistoricBatchesBuilderInterface,
    SetRemovalTimeToHistoricBatchesBuilderInterface
};
use BpmPlatform\Engine\Impl\Cmd\Batch\RemovalTime\SetRemovalTimeToHistoricBatchesCmd;
use BpmPlatform\Engine\Impl\Interceptor\CommandExecutorInterface;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SetRemovalTimeToHistoricBatchesBuilderImpl implements SetRemovalTimeSelectModeForHistoricBatchesBuilderInterface
{
    protected $query;
    protected $ids = [];
    protected $mode;
    protected $removalTime;

    protected $commandExecutor;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function byQuery(HistoricBatchQueryInterface $query): SetRemovalTimeToHistoricBatchesBuilderInterface
    {
        $this->query = $query;
        return $this;
    }

    public function byIds(?array $ids = []): SetRemovalTimeToHistoricBatchesBuilderInterface
    {
        $this->ids = $ids;
        return $this;
    }

    public function absoluteRemovalTime(string $removalTime): SetRemovalTimeToHistoricBatchesBuilderInterface
    {
        EnsureUtil::ensureNull(BadUserRequestException::class, "The removal time modes are mutually exclusive", "mode", $this->mode);
        $this->mode = Mode::ABSOLUTE_REMOVAL_TIME;
        $this->removalTime = $removalTime;
        return $this;
    }

    public function calculatedRemovalTime(): SetRemovalTimeToHistoricBatchesBuilderInterface
    {
        EnsureUtil::ensureNull(BadUserRequestException::class, "The removal time modes are mutually exclusive", "mode", $this->mode);
        $this->mode = Mode::CALCULATED_REMOVAL_TIME;
        return $this;
    }

    public function clearedRemovalTime(): SetRemovalTimeToHistoricBatchesBuilderInterface
    {
        EnsureUtil::ensureNull(BadUserRequestException::class, "The removal time modes are mutually exclusive", "mode", $this->mode);
        $this->mode = Mode::CLEARED_REMOVAL_TIME;
        return $this;
    }

    public function executeAsync(): BatchInterface
    {
        return $this->commandExecutor->execute(new SetRemovalTimeToHistoricBatchesCmd($this));
    }

    public function getQuery(): HistoricBatchQueryInterface
    {
        return $this->query;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getRemovalTime(): string
    {
        return $this->removalTime;
    }

    public function getMode(): int
    {
        return $this->mode;
    }
}
