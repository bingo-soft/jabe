<?php

namespace Jabe\Impl\Identity;

use Jabe\Identity\PasswordPolicyRuleInterface;

class PasswordPolicyLengthRuleImpl implements PasswordPolicyRuleInterface
{
    public const PLACEHOLDER = DefaultPasswordPolicyImpl::PLACEHOLDER_PREFIX . "LENGTH";

    protected $minLength;

    public function __construct(int $minLength)
    {
        $this->minLength = $minLength;
    }

    public function getPlaceholder(): ?string
    {
        return PasswordPolicyLengthRuleImpl::PLACEHOLDER;
    }

    public function getParameters(): array
    {
        $parameter = [];
        $parameter["minLength"] = $this->minLength;
        return $parameter;
    }

    public function execute(?string $password): bool
    {
        return strlen($password) >= $this->minLength;
    }
}
