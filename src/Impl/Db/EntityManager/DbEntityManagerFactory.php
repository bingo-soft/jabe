<?php

namespace Jabe\Impl\Db\EntityManager;

use Jabe\Impl\Cfg\IdGeneratorInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\PersistenceSessionInterface;
use Jabe\Impl\Interceptor\SessionFactoryInterface;

class DbEntityManagerFactory implements SessionFactoryInterface
{
    protected $idGenerator;

    public function __construct(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function getSessionType(): ?string
    {
        return DbEntityManager::class;
    }

    public function openSession(): DbEntityManager
    {
        $persistenceSession = Context::getCommandContext()->getSession(PersistenceSessionInterface::class);
        return new DbEntityManager($this->idGenerator, $persistenceSession);
    }
}
