<?php

namespace Jabe\Telemetry;

interface LicenseKeyDataInterface
{
    /**
     * The name of the customer this license was issued for.
     */
    public function getCustomer(): string;

    /**
     * Camunda uses different license types e.g., when one license includes usage
     * of Cawemo enterprise.
     */
    public function getType(): string;

    /**
     * The expiry date of the license in the format 'YYYY-MM-DD'.
     */
    public function getValidUntil(): string;

    /**
     * A flag indicating if the license is unlimited or expires.
     */
    public function isUnlimited(): bool;

    /**
     * A collection of features that are enabled through this license. Features
     * could be Camunda BPM, Optimize or Cawemo.
     */
    public function getFeatures(): array;

    /**
     * The raw license data. This combines all data fields also included in this
     * class in the form which is stored in the license key String. Note, that
     * this is not the license key as issued to the customer but only contains the
     * plain-text part of it and not the encrypted key.
     */
    public function getRaw(): string;
}
