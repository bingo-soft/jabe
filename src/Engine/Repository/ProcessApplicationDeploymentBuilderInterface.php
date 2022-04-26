<?php

namespace Jabe\Engine\Repository;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;

interface ProcessApplicationDeploymentBuilderInterface extends DeploymentBuilderInterface
{
    /**
     * <p>If this method is called, additional registrations will be created for
     * previous versions of the deployment.</p>
     */
    public function resumePreviousVersions(): ProcessApplicationDeploymentBuilderInterface;

    /**
     * This method defines on what additional registrations will be based.
     * The value will only be recognized if {@link #resumePreviousVersions()} is set.
     * <p>
     * @see ResumePreviousBy
     * @see #resumePreviousVersions()
     * @param resumeByProcessDefinitionKey one of the constants from {@link ResumePreviousBy}
     */
    public function resumePreviousVersionsBy(string $resumePreviousVersionsBy): ProcessApplicationDeploymentBuilderInterface;

    public function deploy(): ProcessApplicationDeploymentInterface;

    // overridden methods //////////////////////////////
    public function addInputStream(string $resourceName, $inputStream): ProcessApplicationDeploymentBuilderInterface;

    public function addClasspathResource(string $resource): ProcessApplicationDeploymentBuilderInterface;

    public function addString(string $resourceName, string $text): ProcessApplicationDeploymentBuilderInterface;

    public function addModelInstance(string $resourceName, BpmnModelInstanceInterface $modelInstance): ProcessApplicationDeploymentBuilderInterface;

    public function addZipInputStream($zipInputStream): ProcessApplicationDeploymentBuilderInterface;

    public function name(string $name): ProcessApplicationDeploymentBuilderInterface;

    public function nameFromDeployment(string $deploymentId): ProcessApplicationDeploymentBuilderInterface;

    public function source(string $source): ProcessApplicationDeploymentBuilderInterface;

    public function enableDuplicateFiltering(bool $deployChangedOnly): ProcessApplicationDeploymentBuilderInterface;

    public function activateProcessDefinitionsOn(string $date): ProcessApplicationDeploymentBuilderInterface;

    public function addDeploymentResources(string $deploymentId): ProcessApplicationDeploymentBuilderInterface;

    public function addDeploymentResourcesById(string $deploymentId, $resourceId): ProcessApplicationDeploymentBuilderInterface;

    public function addDeploymentResourceByName(string $deploymentId, $resourceName): ProcessApplicationDeploymentBuilderInterface;
}
