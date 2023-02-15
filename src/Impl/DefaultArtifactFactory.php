<?php

namespace Jabe\Impl;

use Jabe\{
    ArtifactFactoryInterface,
    ProcessEngineException
};

class DefaultArtifactFactory implements ArtifactFactoryInterface
{
    public function getArtifact(?string $clazz)
    {
        try {
            $clazz = str_replace('.', '\\', $clazz);
            return new $clazz();
        } catch (\Exception $e) {
            throw new ProcessEngineException("couldn't instantiate class " . $clazz, $e);
        }
    }
}
