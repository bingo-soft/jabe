<?php

namespace BpmPlatform\Engine\Impl\Persistence;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Db\EnginePersistenceLogger;
use BpmPlatform\Engine\Impl\Interceptor\{
    SessionInterface,
    SessionFactoryInterface
};
use BpmPlatform\Engine\Impl\Util\ReflectUtil;

class GenericManagerFactory implements SessionFactoryInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $managerImplementation;

    public function __construct(string $className)
    {
        $this->managerImplementation = $className;
    }

    public function getSessionType(): string
    {
        return $this->managerImplementation;
    }

    public function openSession(): SessionInterface
    {
        try {
            return new $managerImplementation();
        } catch (\Exception $e) {
            //throw LOG.instantiateSessionException(managerImplementation.getName(), e);
            throw $e;
        }
    }
}
