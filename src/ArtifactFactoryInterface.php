<?php

namespace Jabe;

interface ArtifactFactoryInterface
{
    /**
     *
     * @param clazz of the artifact to create
     * @return mixed the instance of the fullyQualifiedClassName
     */
    public function getArtifact(string $clazz);
}
