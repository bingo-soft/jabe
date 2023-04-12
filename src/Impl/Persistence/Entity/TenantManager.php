<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    ListQueryParameterObject,
    TenantCheck
};
use Jabe\Impl\Persistence\AbstractManager;

class TenantManager extends AbstractManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function configureTenantCheck(TenantCheck $tenantCheck): void
    {
        if ($this->isTenantCheckEnabled()) {
            $currentAuthentication = $this->getCurrentAuthentication();
            $tenantCheck->setTenantCheckEnabled(true);
            $tenantCheck->setAuthTenantIds($currentAuthentication->getTenantIds());
        } else {
            $tenantCheck->setTenantCheckEnabled(false);
            $tenantCheck->setAuthTenantIds(null);
        }
    }

    public function configureQuery(/*ListQueryParameterObjec*/$parameters, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        if ($parameters instanceof ListQueryParameterObject) {
            $tenantCheck = $parameters->getTenantCheck();
            $this->configureTenantCheck($tenantCheck);
            return $parameters;
        } else {
            $queryObject = new ListQueryParameterObject();
            $queryObject->setParameter($parameters);
            return $this->configureQuery($queryObject);
        }
    }

    public function isAuthenticatedTenant(?string $tenantId): bool
    {
        if ($tenantId !== null && $this->isTenantCheckEnabled()) {
            $currentAuthentication = $this->getCurrentAuthentication();
            $authenticatedTenantIds = $currentAuthentication->getTenantIds();
            if (!empty($authenticatedTenantIds)) {
                return in_array($tenantId, $authenticatedTenantIds);
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function isTenantCheckEnabled(): bool
    {
        return Context::getProcessEngineConfiguration()->isTenantCheckEnabled()
            && Context::getCommandContext()->isTenantCheckEnabled()
            && $this->getCurrentAuthentication() !== null
            && !$this->getAuthorizationManager()->isAdmin($this->getCurrentAuthentication());
    }
}
