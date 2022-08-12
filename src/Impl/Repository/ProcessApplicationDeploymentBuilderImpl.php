<?php

namespace Jabe\Impl\Repository;

use Jabe\Application\ProcessApplicationReferenceInterface;
use Jabe\Impl\RepositoryServiceImpl;
use Jabe\Repository\{
    ProcessApplicationDeploymentInterface,
    ProcessApplicationDeploymentBuilderInterface,
    ResumePreviousBy
};

class ProcessApplicationDeploymentBuilderImpl extends DeploymentBuilderImpl implements ProcessApplicationDeploymentBuilderInterface
{
    protected $processApplicationReference;
    protected $isResumePreviousVersions = false;
    protected $resumePreviousVersionsBy = ResumePreviousBy::RESUME_BY_PROCESS_DEFINITION_KEY;

    public function __construct(RepositoryServiceImpl $repositoryService, ProcessApplicationReferenceInterface $reference)
    {
        parent::__construct($repositoryService);
        $this->processApplicationReference = $reference;
        $this->source(ProcessApplicationDeploymentInterface::PROCESS_APPLICATION_DEPLOYMENT_SOURCE);
    }

    public function resumePreviousVersions(): ProcessApplicationDeploymentBuilderInterface
    {
        $this->isResumePreviousVersions = true;
        return $this;
    }

    public function resumePreviousVersionsBy(string $resumePreviousVersionsBy): ProcessApplicationDeploymentBuilderInterface
    {
        $this->resumePreviousVersionsBy = $resumePreviousVersionsBy;
        return $this;
    }
    // overrides from parent ////////////////////////////////////////////////

    public function isResumePreviousVersions(): bool
    {
        return $this->isResumePreviousVersions;
    }

    public function getProcessApplicationReference(): ProcessApplicationReferenceInterface
    {
        return $this->processApplicationReference;
    }

    public function getResumePreviousVersionsBy(): string
    {
        return $this->resumePreviousVersionsBy;
    }
}
