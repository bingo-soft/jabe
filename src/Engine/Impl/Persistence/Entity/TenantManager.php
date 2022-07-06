<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    ListQueryParameterObject,
    TenantCheck
};
use Jabe\Engine\Impl\Identity\Authentication;
use Jabe\Engine\Impl\Persistence\AbstractManager;

class TenantManager extends AbstractManager
{
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

    public function configureQuery($parameters)
    {
        if ($parameters instanceof ListQueryParameterObject) {
            $tenantCheck = $query->getTenantCheck();
            $this->configureTenantCheck($tenantCheck);
            return $query;
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
