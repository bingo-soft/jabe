<?php

namespace Jabe\Impl;

use Jabe\History\HistoricTaskInstanceReportInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class ExecuteDurationCmd implements CommandInterface
{
    private $scope;

    public function __construct(HistoricTaskInstanceReportInterface $scope)
    {
        $this->scope = $scope;
    }

    public function execute(CommandContext $commandContext)
    {
        return $this->scope->executeDuration($commandContext);
    }
}
