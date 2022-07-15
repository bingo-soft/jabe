<?php

namespace Jabe\Engine\Impl\Batch\Update;

use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    DeploymentMappings
};

class UpdateProcessInstancesSuspendStateBatchConfiguration extends BatchConfiguration
{
    protected $suspended;

    public function __construct(array $ids, $mappingsOrSuspended, $suspended = null)
    {
        if ($mappingsOrSuspended instanceof DeploymentMappings) {
            parent::__construct($ids, $mappingsOrSuspended);
        }
        if (is_bool($mappingsOrSuspended)) {
            $this->suspended = $mappingsOrSuspended;
        } elseif (is_bool($suspended)) {
            $this->suspended = $suspended;
        }
    }

    public function getSuspended(): bool
    {
        return $this->suspended;
    }
}
