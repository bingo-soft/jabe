<?php

namespace Jabe\Impl;

use Jabe\History\HistoricProcessInstanceReportInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class ExecuteDurationReportCmd implements CommandInterface
{
    private $scope;

    public function __construct(HistoricProcessInstanceReportInterface $scope)
    {
        $this->scope = $scope;
    }

    public function execute(CommandContext $commandContext)
    {
        return $this->scope->executeDurationReport($commandContext);
    }
}
