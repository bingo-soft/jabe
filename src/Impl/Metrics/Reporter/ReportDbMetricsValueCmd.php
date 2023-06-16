<?php

namespace Jabe\Impl\Metrics\Reporter;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\MeterLogEntity;
use Jabe\Impl\Util\ClockUtil;

class ReportDbMetricsValueCmd implements CommandInterface
{
    protected $reporterId;
    protected $name;
    protected $value;

    public function __construct(?string $reporterId, ?string $name, int $value)
    {
        $this->reporterId = $reporterId;
        $this->name = $name;
        $this->value = $value;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getMeterLogManager()->insert(new MeterLogEntity($this->name, $this->reporterId, $this->value, ClockUtil::getCurrentTime()->format('Y-m-d H:i:s')));
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
