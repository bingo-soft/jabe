<?php

namespace Jabe\Engine\Impl\Persistence;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Db\EnginePersistenceLogger;
use Jabe\Engine\Impl\Interceptor\{
    SessionInterface,
    SessionFactoryInterface
};

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
            $managerImplementation = $this->managerImplementation;
            return new $managerImplementation();
        } catch (\Exception $e) {
            //throw LOG.instantiateSessionException(managerImplementation.getName(), e);
            throw $e;
        }
    }
}
