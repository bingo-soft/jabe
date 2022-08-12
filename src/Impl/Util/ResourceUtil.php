<?php

namespace Jabe\Impl\Util;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    ResourceEntity
};

class ResourceUtil
{
    //private static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;

    /**
     * Parse a camunda:resource attribute and loads the resource depending on the url scheme.
     * Supported URL schemes are <code>classpath://</code> and <code>deployment://</code>.
     * If the scheme is omitted <code>classpath://</code> is assumed.
     *
     * @param resourcePath the path to the resource to load
     * @param deployment the deployment to load resources from
     * @return the resource content as String
     */
    public static function loadResourceContent(string $resourcePath, DeploymentEntity $deployment): string
    {
        $pathSplit = explode("://", $resourcePath);

        $resourceType = null;
        if (count($pathSplit) == 1) {
            $resourceType = "classpath";
        } else {
            $resourceType = $pathSplit[0];
        }

        $resourceLocation = $pathSplit[count($pathSplit) - 1];

        $resourceBytes = null;

        if ($resourceType == "classpath") {
            $resourceAsStream = null;
            try {
                $resourceAsStream = ReflectUtil::getResourceAsStream($resourceLocation);
                if ($resourceAsStream !== null) {
                    $resourceBytes = IoUtil::readInputStream($resourceAsStream, $resourcePath);
                }
            } finally {
                IoUtil::closeSilently($resourceAsStream);
            }
        } elseif ($resourceType == "deployment") {
            $resourceEntity = $deployment->getResource($resourceLocation);
            if ($resourceEntity !== null) {
                $resourceBytes = $resourceEntity->getBytes();
            }
        }

        if ($resourceBytes !== null) {
            return $resourceBytes;
        } else {
            throw new \Exception(sprintf("cannotFindResource %s", $resourcePath));
        }
    }
}
