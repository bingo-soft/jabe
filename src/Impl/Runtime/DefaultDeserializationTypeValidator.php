<?php

namespace Jabe\Impl\Runtime;

use Jabe\Runtime\WhitelistingDeserializationTypeValidatorInterface;

class DefaultDeserializationTypeValidator implements WhitelistingDeserializationTypeValidatorInterface
{
    protected $allowedClasses = [];
    protected $allowedPackages = [];

    public function setAllowedClasses(?string $deserializationAllowedClasses = ''): void
    {
        $this->extractElements($deserializationAllowedClasses, $this->allowedClasses);
    }

    public function setAllowedPackages(?string $deserializationAllowedPackages = ''): void
    {
        $this->extractElements($deserializationAllowedPackages, $this->allowedPackages);
    }

    public function validate(?string $className): bool
    {
        if (empty($className)) {
            return true;
        }
        return $this->isPackageAllowed($className) || $this->isClassNameAllowed($className);
    }

    protected function isPackageAllowed(?string $className, ?array $allowedPackages = null): bool
    {
        $allowedPackages = $allowedPackages ?? $this->allowedPackages;
        foreach ($allowedPackages as $allowedPackage) {
            if (!empty($allowedPackage) && str_starts_with($className, $allowedPackage)) {
                return true;
            }
        }
        return false;
    }

    protected function isClassNameAllowed(?string $className): bool
    {
        return in_array($className, $this->allowedClasses);
    }

    protected function extractElements(?string $allowedElements = '', array &$set = []): void
    {
        if (!empty($set)) {
            $set = [];
        }
        if (empty($allowedElements)) {
            return;
        }
        $allowedElementsSanitized = preg_replace('/\s+/', '', $allowedElements);
        if (empty($allowedElementsSanitized)) {
            return;
        }
        $classes = explode(',', $allowedElementsSanitized);
        foreach ($classes as $className) {
            $set[] = $className;
        }
    }
}
