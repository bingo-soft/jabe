<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\{
    ArtifactFactoryInterface,
    ProcessEngineException
};

class DefaultArtifactFactory implements ArtifactFactoryInterface
{
    public function getArtifact(string $clazz)
    {
        try {
            return new $clazz();
        } catch (\Exception $e) {
            throw new ProcessEngineException("couldn't instantiate class " . $clazz, $e);
        }
    }
}
