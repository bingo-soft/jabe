<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Runtime\ProcessElementInstanceInterface;
use Jabe\Engine\Impl\Util\ClassNameUtil;

class ProcessElementInstanceImpl implements ProcessElementInstanceInterface
{
    protected static $NO_IDS = [];

    protected $id;
    protected $parentActivityInstanceId;
    protected $processInstanceId;
    protected $processDefinitionId;

    public function getId(): ?string
    {
        return $this->id;
    }
    public function setId(string $id): void
    {
        $this->id = $id;
    }
    public function getParentActivityInstanceId(): ?string
    {
        return $this->parentActivityInstanceId;
    }
    public function setParentActivityInstanceId(string $parentActivityInstanceId): void
    {
        $this->parentActivityInstanceId = $parentActivityInstanceId;
    }
    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }
    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }
    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }
    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", parentActivityInstanceId=" . $this->parentActivityInstanceId
                . ", processInstanceId=" . $this->processInstanceId
                . ", processDefinitionId=" . $this->processDefinitionId
                . "]";
    }
}
