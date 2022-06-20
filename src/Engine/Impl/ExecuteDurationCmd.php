<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\History\HistoricTaskInstanceReportInterface;
use Jabe\Engine\Impl\Interceptor\{
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
