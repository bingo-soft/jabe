<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    PropertyEntity,
    PropertyManager,
    ResourceEntity,
    ResourceManager
};

class DeleteLicenseKeyCmd extends LicenseCmd implements CommandInterface
{
    private $deleteProperty;
    private $updateTelemetry;

    public function __construct(bool $deleteProperty, ?bool $updateTelemetry = true)
    {
        $this->deleteProperty = $deleteProperty;
        $this->updateTelemetry = $updateTelemetry;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkDeleteLicenseKey");

        $resourceManager = $commandContext->getResourceManager();
        $propertyManager = $commandContext->getPropertyManager();

        // lock the property
        $licenseProperty = $propertyManager->findPropertyById(self::LICENSE_KEY_BYTE_ARRAY_ID);

        // delete license key BLOB
        $licenseKey = $resourceManager->findLicenseKeyResource();
        if ($licenseKey !== null) {
            $resourceManager->delete($licenseKey);
        }

        // always delete license key legacy property if it still exists
        (new DeletePropertyCmd(self::LICENSE_KEY_PROPERTY_NAME))->execute($commandContext);

        if ($this->deleteProperty) {
            // delete license key byte array id
            (new DeletePropertyCmd(self::LICENSE_KEY_BYTE_ARRAY_ID))->execute($commandContext);
        }

        if ($this->updateTelemetry) {
            $commandContext->getProcessEngineConfiguration()->getManagementService()->setLicenseKeyForTelemetry(null);
        }

        return null;
    }
}
