<?php

namespace Jabe\Repository;

interface DeploymentInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function getDeploymentTime(): ?string;

    public function getSource(): ?string;

    /**
     * Returns the id of the tenant this deployment belongs to. Can be <code>null</code>
     * if the deployment belongs to no single tenant.
     */
    public function getTenantId(): ?string;
}
