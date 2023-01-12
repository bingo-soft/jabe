<?php

namespace Jabe\Impl\Identity;

use Jabe\Identity\PasswordPolicyRuleInterface;

class PasswordPolicyDigitRuleImpl implements PasswordPolicyRuleInterface
{
    public const PLACEHOLDER = DefaultPasswordPolicyImpl::PLACEHOLDER_PREFIX . "DIGIT";

    protected $minDigit;

    public function __construct(int $minDigit)
    {
        $this->minDigit = $minDigit;
    }

    public function getPlaceholder(): ?string
    {
        return PasswordPolicyDigitRuleImpl::PLACEHOLDER;
    }

    public function getParameters(): array
    {
        $parameter = [];
        $parameter["minDigit"] = $this->minDigit;
        return $parameter;
    }
}
