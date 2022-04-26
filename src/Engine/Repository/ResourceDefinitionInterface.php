<?php

namespace Jabe\Engine\Repository;

interface ResourceDefinitionInterface
{
    /** unique identifier */
    public function getId(): string;

    /** category name which is derived from the targetNamespace attribute in the definitions element */
    public function getCategory(): string;

    /** label used for display purposes */
    public function getName(): string;

    /** unique name for all versions this definition */
    public function getKey(): string;

    /** version of this definition */
    public function getVersion(): int;

    /** name of {@link RepositoryService#getResourceAsStream(String, String) the resource} of this definition */
    public function getResourceName(): string;

    /** The deployment in which this definition is contained. */
    public function getDeploymentId(): ?string;

    /** The diagram resource name for this definition if exist */
    public function getDiagramResourceName(): ?string;

    /**
     * The id of the tenant this definition belongs to. Can be <code>null</code>
     * if the definition belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /** History time to live. Is taken into account in history cleanup. */
    public function getHistoryTimeToLive(): string;
}
