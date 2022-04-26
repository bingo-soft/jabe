<?php

namespace Jabe\Engine;

interface ArtifactFactoryInterface
{
    /**
     *
     * @param clazz of the artifact to create
     * @return the instance of the fullyQualifiedClassName
     */
    public function getArtifact(string $clazz);
}
