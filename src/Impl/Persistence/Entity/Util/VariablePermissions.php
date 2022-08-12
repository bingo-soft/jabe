<?php

namespace Jabe\Impl\Persistence\Entity\Util;

use Jabe\Authorization\{
    HistoricTaskPermissions,
    PermissionInterface,
    ProcessDefinitionPermissions
};

class VariablePermissions
{
    protected $processDefinitionPermission;
    protected $historicTaskPermission;

    public function __construct(bool $ensureSpecificVariablePermission)
    {
        if ($ensureSpecificVariablePermission) {
            $this->processDefinitionPermission = ProcessDefinitionPermissions::readHistoryVariable();
            $this->historicTaskPermission = HistoricTaskPermissions::readVariable();
        } else {
            $this->processDefinitionPermission = ProcessDefinitionPermissions::readHistory();
            $this->historicTaskPermission = HistoricTaskPermissions::read();
        }
    }

    public function getProcessDefinitionPermission(): PermissionInterface
    {
        return $this->processDefinitionPermission;
    }

    public function getHistoricTaskPermission(): PermissionInterface
    {
        return $this->historicTaskPermission;
    }
}
