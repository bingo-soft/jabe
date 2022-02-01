<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Application\ProcessApplicationRegistrationInterface;
use BpmPlatform\Engine\Repository\{
    DeploymentWithDefinitionsInterface,
    ProcessApplicationDeploymentInterface
};

class ProcessApplicationDeploymentImpl implements ProcessApplicationDeploymentInterface
{
    protected $deployment;
    protected $registration;

    public function __construct(DeploymentWithDefinitionsInterface $deployment, ProcessApplicationRegistrationInterface $registration)
    {
        $this->deployment = $deployment;
        $this->registration = $registration;
    }

    public function getId(): ?string
    {
        return $this->deployment->getId();
    }

    public function getName(): string
    {
        return $this->deployment->getName();
    }

    public function getDeploymentTime(): string
    {
        return $this->deployment->getDeploymentTime();
    }

    public function getSource(): string
    {
        return $this->deployment->getSource();
    }

    public function getTenantId(): ?string
    {
        return $this->deployment->getTenantId();
    }

    public function getProcessApplicationRegistration(): ?ProcessApplicationRegistrationInterface
    {
        return $this->registration;
    }

    public function getDeployedProcessDefinitions(): array
    {
        return $this->deployment->getDeployedProcessDefinitions();
    }

    /*public function getDeployedCaseDefinitions(): array
    {
        return $this->deployment->getDeployedCaseDefinitions();
    }

    public function getDeployedDecisionDefinitions(): array
    {
        return $this->deployment->getDeployedDecisionDefinitions();
    }

    public function getDeployedDecisionRequirementsDefinitions(): array
    {
        return $this->deployment->getDeployedDecisionRequirementsDefinitions();
    }*/
}
