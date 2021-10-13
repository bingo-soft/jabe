<?php

namespace BpmPlatform\Engine\Repository;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;

interface DeploymentBuilderInterface
{
    public function addInputStream(string $resourceName, $inputStream): DeploymentBuilderInterface;
    public function addClasspathResource(string $resource): DeploymentBuilderInterface;
    public function addString(string $resourceName, string $text): DeploymentBuilderInterface;

    public function addBpmnModelInstance(
        string $resourceName,
        BpmnModelInstanceInterface $modelInstance
    ): DeploymentBuilderInterface;

    public function addDeploymentResources(string $deploymentId): DeploymentBuilderInterface;

    public function addDeploymentResourceById(string $deploymentId, string $resourceId): DeploymentBuilderInterface;

    public function addDeploymentResourcesById(string $deploymentId, array $resourceIds): DeploymentBuilderInterface;

    public function addDeploymentResourceByName(string $deploymentId, string $resourceName): DeploymentBuilderInterface;

    public function addDeploymentResourcesByName(
        string $deploymentId,
        array $resourceNames
    ): DeploymentBuilderInterface;

    public function name(string $name): DeploymentBuilderInterface;

    public function enableDuplicateFiltering(bool $deployChangedOnly): DeploymentBuilderInterface;

    public function activateProcessDefinitionsOn(string $date): DeploymentBuilderInterface;

    public function source(string $source): DeploymentBuilderInterface;

    public function deploy(): DeploymentInterface;

    public function deployWithResult(): DeploymentWithDefinitionsInterface;

    public function getResourceNames(): array;

    public function tenantId(string $tenantId): DeploymentBuilderInterface;
}
