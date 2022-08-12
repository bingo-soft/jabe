<?php

namespace Jabe\Telemetry;

interface ProductInterface
{
    /**
     * The name of the product (i.e., Camunda BPM Runtime).
     */
    public function getName(): string;

    /**
     * The version of the process engine (i.e., 7.X.Y).
     */
    public function getVersion(): string;

    /**
     * The edition of the product (i.e., either community or enterprise).
     */
    public function getEdition(): string;

    /**
     * Information about the technical internals and the environment of the
     * Camunda installation.
     */
    public function getInternals(): InternalsInterface;
}
