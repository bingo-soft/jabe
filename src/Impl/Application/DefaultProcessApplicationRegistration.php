<?php

namespace Jabe\Impl\Application;

use Jabe\Application\{
    ProcessApplicationReferenceInterface,
    ProcessApplicationRegistrationInterface
};

class DefaultProcessApplicationRegistration implements ProcessApplicationRegistrationInterface
{
    protected $deploymentIds = [];
    protected $processEngineName;
    protected $reference;

    /**
     * @param reference
     */
    public function __construct(ProcessApplicationReferenceInterface $reference, array $deploymentIds, ?string $processEnginenName)
    {
        $this->reference = $reference;
        $this->deploymentIds = $deploymentIds;
        $this->processEngineName = $processEnginenName;
    }

    public function getDeploymentIds(): array
    {
        return $this->deploymentIds;
    }

    public function getProcessEngineName(): ?string
    {
        return $this->processEngineName;
    }

    public function getReference(): ProcessApplicationReferenceInterface
    {
        return $this->reference;
    }
}
