<?php

namespace Jabe\Engine\Impl\Metrics\Reporter;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\MeterLogEntity;
use Jabe\Engine\Impl\Util\ClockUtil;

class ReportDbMetricsValueCmd implements CommandInterface
{
    protected $reporterId;
    protected $name;
    protected $value;

    public function __construct(string $reporterId, string $name, int $value)
    {
        $this->reporterId = $reporterId;
        $this->name = $name;
        $this->value = $value;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getMeterLogManager()->insert(new MeterLogEntity($this->name, $this->reporterId, $this->value, ClockUtil::getCurrentTime()->format('c')));
        return null;
    }
}
