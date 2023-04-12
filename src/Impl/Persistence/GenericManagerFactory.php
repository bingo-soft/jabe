<?php

namespace Jabe\Impl\Persistence;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Db\EnginePersistenceLogger;
use Jabe\Impl\Interceptor\{
    SessionInterface,
    SessionFactoryInterface
};

class GenericManagerFactory implements SessionFactoryInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $managerImplementation;
    protected $jobExecutorState = [];

    public function __construct(?string $className, ...$args)
    {
        $this->managerImplementation = $className;
        if (!empty($args)) {
            $this->jobExecutorState = $args;
        }
    }

    public function getSessionType(): ?string
    {
        return $this->managerImplementation;
    }

    public function openSession(): SessionInterface
    {
        try {
            $managerImplementation = $this->managerImplementation;
            return new $managerImplementation(...$this->jobExecutorState);
        } catch (\Exception $e) {
            //throw LOG.instantiateSessionException(managerImplementation.getName(), e);
            throw $e;
        }
    }
}
