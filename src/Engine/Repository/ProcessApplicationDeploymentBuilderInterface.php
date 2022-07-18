<?php

namespace Jabe\Engine\Repository;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;

interface ProcessApplicationDeploymentBuilderInterface extends DeploymentBuilderInterface
{
    /**
     * <p>If this method is called, additional registrations will be created for
     * previous versions of the deployment.</p>
     */
    public function resumePreviousVersions(): DeploymentBuilderInterface;

    /**
     * This method defines on what additional registrations will be based.
     * The value will only be recognized if {@link #resumePreviousVersions()} is set.
     * <p>
     * @see ResumePreviousBy
     * @see #resumePreviousVersions()
     * @param resumeByProcessDefinitionKey one of the constants from {@link ResumePreviousBy}
     */
    public function resumePreviousVersionsBy(string $resumePreviousVersionsBy): DeploymentBuilderInterface;

    public function deploy(): ProcessApplicationDeploymentInterface;

    // overridden methods //////////////////////////////
    public function addInputStream(string $resourceName, $inputStream): DeploymentBuilderInterface;

    public function addClasspathResource(string $resource): DeploymentBuilderInterface;

    public function addString(string $resourceName, string $text): DeploymentBuilderInterface;

    public function addModelInstance(string $resourceName, BpmnModelInstanceInterface $modelInstance): DeploymentBuilderInterface;

    //public function addZipInputStream($zipInputStream): ProcessApplicationDeploymentBuilderInterface;

    public function name(string $name): DeploymentBuilderInterface;

    public function nameFromDeployment(string $deploymentId): DeploymentBuilderInterface;

    public function source(string $source): DeploymentBuilderInterface;

    public function enableDuplicateFiltering(bool $deployChangedOnly = false): DeploymentBuilderInterface;

    public function activateProcessDefinitionsOn(string $date): DeploymentBuilderInterface;

    public function addDeploymentResources(string $deploymentId): DeploymentBuilderInterface;

    public function addDeploymentResourcesById(string $deploymentId, $resourceId): DeploymentBuilderInterface;

    public function addDeploymentResourceByName(string $deploymentId, $resourceName): DeploymentBuilderInterface;
}
