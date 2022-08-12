<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\ResourceEntity;
use Jabe\Impl\Telemetry\Dto\LicenseKeyDataImpl;
use Jabe\Impl\Util\EnsureUtil;

class SetLicenseKeyCmd extends LicenseCmd implements CommandInterface
{
    protected $licenseKey;

    public function __construct(string $licenseKey)
    {
        $this->licenseKey = $licenseKey;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("licenseKey", "licenseKey", $this->licenseKey);

        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkSetLicenseKey");

        $resourceManager = $commandContext->getResourceManager();
        $key = $resourceManager->findLicenseKeyResource();
        if ($key !== null) {
            (new DeleteLicenseKeyCmd(false, false))->execute($commandContext);
        }
        $key = new ResourceEntity();
        $key->setName(self::LICENSE_KEY_PROPERTY_NAME);
        $key->setBytes($this->licenseKey->getBytes());
        // set license key as byte array BLOB
        $resourceManager->insertResource($key);

        // set license key byte array id property
        $scope = $this;
        $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext, $key) {
            $cmd = new SetPropertyCmd($scope::LICENSE_KEY_BYTE_ARRAY_ID, $key->getId());
            return $cmd->execute($commandContext);
        });

        // cleanup legacy property
        $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $cmd = new DeletePropertyCmd($scope::LICENSE_KEY_PROPERTY_NAME);
            return $cmd->execute($commandContext);
        });

        // add raw license to telemetry data if not there already
        $managementService = $commandContext->getProcessEngineConfiguration()->getManagementService();
        $currentLicenseData = $managementService->getLicenseKeyFromTelemetry();
        // only report license body without signature
        $licenseKeyData = LicenseKeyDataImpl::fromRawString($this->licenseKey);
        if ($currentLicenseData === null || $licenseKeyData->getRaw() != $currentLicenseData->getRaw()) {
            $managementService->setLicenseKeyForTelemetry($licenseKeyData);
        }

        return null;
    }
}
