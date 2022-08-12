<?php

namespace Jabe\Impl\Metrics\Reporter;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class MetricsCollectionCmd implements CommandInterface
{
    protected $logs = [];

    public function __construct(array $logs)
    {
        $this->logs = $logs;
    }

    public function execute(CommandContext $commandContext)
    {
        foreach ($this->logs as $meterLogEntity) {
            $commandContext->getMeterLogManager()->insert($meterLogEntity);
        }
        return null;
    }
}
