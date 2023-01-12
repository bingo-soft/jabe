<?php

namespace Jabe\Application;

interface ProcessApplicationInfoInterface
{
    /** constant for the servlet context path property */
    public const PROP_CONTEXT_PATH = "contextPath";

    /**
     * @return string the name of the process application
     */
    public function getName(): ?string;

    /**
     * @return a list of ProcessApplicationDeploymentInfo objects that
     *         provide information about the deployments made by the process
     *         application to the process engine(s).
     */
    public function getDeploymentInfo(): array;

    /**
     * <p>Provides access to a list of process application-provided properties.</p>
     *
     * <p>This class provides a set of constants for commonly-used properties</p>
     *
     * @see ProcessApplicationInfo#PROP_CONTEXT_PATH
     */
    public function getProperties(): array;
}
