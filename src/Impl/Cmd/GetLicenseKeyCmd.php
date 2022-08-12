<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetLicenseKeyCmd extends LicenseCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkCamundaAdminOrPermission("checkReadLicenseKey");

        // case I: license is stored as BLOB
        $licenseResource = $commandContext->getResourceManager()->findLicenseKeyResource();
        if ($licenseResource !== null) {
            return $licenseResource->getBytes();
        }

        // case II: license is stored in properties
        $licenseProperty = $commandContext->getPropertyManager()->findPropertyById(self::LICENSE_KEY_PROPERTY_NAME);
        if ($licenseProperty !== null) {
            return $licenseProperty->getValue();
        }

        return null;
    }
}
