<?php

namespace Jabe\Impl\Telemetry\Dto;

use Jabe\Telemetry\LicenseKeyDataInterface;

class LicenseKeyDataImpl implements LicenseKeyDataInterface
{
    public const SERIALIZED_VALID_UNTIL = "valid-until";
    public const SERIALIZED_IS_UNLIMITED = "unlimited";

    protected $customer;
    protected $type;
    protected $validUntil;
    protected $isUnlimited;
    protected $features;
    protected $raw;

    public function __construct(?string $customer, ?string $type, ?string $validUntil, bool $isUnlimited, array $features, ?string $raw)
    {
        $this->customer = $customer;
        $this->type = $type;
        $this->validUntil = $validUntil;
        $this->isUnlimited = $isUnlimited;
        $this->features = $features;
        $this->raw = $raw;
    }

    public static function fromRawString(?string $rawLicense): LicenseKeyDataImpl
    {
        $licenseKeyRawString = strpos($rawLicense, ";") !== -1 ? substr($rawLicense, strpos($rawLicense, ";") + 1, strlen($rawLicense)) : $rawLicense;
        return new LicenseKeyDataImpl(null, null, null, null, null, $licenseKeyRawString);
    }

    public function __toString()
    {
        return json_encode([
            'customer' => $this->customer,
            'type' => $this->type,
            'validUntil' => $this->validUntil,
            'isUnlimited' => $this->isUnlimited,
            'features' => $this->features,
            'raw' => $this->raw
        ]);
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(?string $customer): void
    {
        $this->customer = $customer;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getValidUntil(): ?string
    {
        return $this->validUntil;
    }

    public function setValidUntil(?string $validUntil): void
    {
        $this->validUntil = $validUntil;
    }

    public function isUnlimited(): bool
    {
        return $this->isUnlimited;
    }

    public function setUnlimited(bool $isUnlimited): void
    {
        $this->isUnlimited = $isUnlimited;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function setFeatures(array $features): void
    {
        $this->features = $features;
    }

    public function getRaw(): ?string
    {
        return $this->raw;
    }

    public function setRaw(?string $raw): void
    {
        $this->raw = $raw;
    }
}
