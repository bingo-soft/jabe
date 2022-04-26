<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Filter\FilterInterface;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class SaveFilterCmd implements CommandInterface, \Serializable
{
    protected $filter;

    public function __construct(?FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function serialize()
    {
        return json_encode([
            'filter' => serialize($this->filter)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->filter = unserialize($json->filter);
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("filter", "filter", $this->filter);

        $operation = $this->filter->getId() == null ? UserOperationLogEntryInterface::OPERATION_TYPE_CREATE : UserOperationLogEntryInterface::PERATION_TYPE_UPDATE;

        $savedFilter = $commandContext
            ->getFilterManager()
            ->insertOrUpdateFilter($filter);

        $commandContext->getOperationLogManager()->logFilterOperation($operation, $filter->getId());

        return $savedFilter;
    }
}
