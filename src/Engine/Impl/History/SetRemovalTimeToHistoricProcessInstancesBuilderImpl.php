<?php

namespace Jabe\Engine\Impl\History;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Batch\History\HistoricProcessInstanceQueryInterface;
use Jabe\Engine\History\{
    SetRemovalTimeSelectModeForHistoricProcessInstancesBuilderInterface,
    SetRemovalTimeToHistoricProcessInstancesBuilderInterface
};
use Jabe\Engine\Impl\Cmd\Batch\RemovalTime\SetRemovalTimeToHistoricProcessInstancesCmd;
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Util\EnsureUtil;

class SetRemovalTimeToHistoricProcessInstancesBuilderImpl implements SetRemovalTimeSelectModeForHistoricProcessInstancesBuilderInterface
{
    protected $query;
    protected $ids = [];
    protected $mode;
    protected $removalTime;
    protected $isHierarchical = false;

    protected $commandExecutor;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function byQuery(HistoricProcessInstanceQueryInterface $query): SetRemovalTimeToHistoricProcessInstancesBuilderInterface
    {
        $this->query = $query;
        return $this;
    }

    public function byIds(?array $ids = []): SetRemovalTimeToHistoricProcessInstancesBuilderInterface
    {
        $this->ids = $ids;
        return $this;
    }

    public function absoluteRemovalTime(string $removalTime): SetRemovalTimeToHistoricProcessInstancesBuilderInterface
    {
        EnsureUtil::ensureNull(BadUserRequestException::class, "The removal time modes are mutually exclusive", "mode", $this->mode);
        $this->mode = Mode::ABSOLUTE_REMOVAL_TIME;
        $this->removalTime = $removalTime;
        return $this;
    }

    public function calculatedRemovalTime(): SetRemovalTimeToHistoricProcessInstancesBuilderInterface
    {
        EnsureUtil::ensureNull(BadUserRequestException::class, "The removal time modes are mutually exclusive", "mode", $this->mode);
        $this->mode = Mode::CALCULATED_REMOVAL_TIME;
        return $this;
    }

    public function clearedRemovalTime(): SetRemovalTimeToHistoricProcessInstancesBuilderInterface
    {
        EnsureUtil::ensureNull(BadUserRequestException::class, "The removal time modes are mutually exclusive", "mode", $this->mode);
        $this->mode = Mode::CLEARED_REMOVAL_TIME;
        return $this;
    }

    public function executeAsync(): BatchInterface
    {
        return $this->commandExecutor->execute(new SetRemovalTimeToHistoricProcessInstancesCmd($this));
    }

    public function getQuery(): HistoricProcessInstanceQueryInterface
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

    public function hierarchical(): SetRemovalTimeToHistoricProcessInstancesBuilderInterface
    {
        $this->isHierarchical = true;
        return $this;
    }

    public function isHierarchical(): bool
    {
        return $this->isHierarchical;
    }
}
