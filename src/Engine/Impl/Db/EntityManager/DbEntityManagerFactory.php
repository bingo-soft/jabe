<?php

namespace Jabe\Engine\Impl\Db\EntityManager;

use Jabe\Engine\Impl\Cfg\IdGeneratorInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\PersistenceSessionInterface;
use Jabe\Engine\Impl\Interceptor\SessionFactoryInterface;

class DbEntityManagerFactory implements SessionFactoryInterface
{
    protected $idGenerator;

    public function __construct(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function getSessionType(): string
    {
        return DbEntityManager::class;
    }

    public function openSession(): DbEntityManager
    {
        $persistenceSession = Context::getCommandContext()->getSession(PersistenceSessionInterface::class);
        return new DbEntityManager($this->idGenerator, $persistenceSession);
    }
}
