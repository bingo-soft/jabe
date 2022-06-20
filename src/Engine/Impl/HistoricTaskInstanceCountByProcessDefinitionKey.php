<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\History\HistoricProcessInstanceReportInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class HistoricTaskInstanceCountByProcessDefinitionKey implements CommandInterface
{
    private $scope;

    public function __construct(HistoricProcessInstanceReportInterface $scope)
    {
        $this->scope = $scope;
    }

    public function execute(CommandContext $commandContext)
    {
        return $this->scope->executeCountByProcessDefinitionKey($commandContext);
    }
}
