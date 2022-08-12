<?php

namespace Jabe\Impl\Identity;

use Jabe\Identity\PasswordPolicyRuleInterface;

class PasswordPolicyUpperCaseRuleImpl implements PasswordPolicyRuleInterface
{
    public const PLACEHOLDER = DefaultPasswordPolicyImpl::PLACEHOLDER_PREFIX . "UPPERCASE";

    protected $minUpperCase;

    public function __construct(int $minUpperCase)
    {
        $this->minUpperCase = $minUpperCase;
    }

    public function getPlaceholder(): string
    {
        return PasswordPolicyUpperCaseRuleImpl::PLACEHOLDER;
    }

    public function getParameters(): array
    {
        $parameter = [];
        $parameter["minUpperCase"] = $this->minUpperCase;
        return $parameter;
    }

    public function execute(string $password): bool
    {
        $upperCaseCount = 0;
        foreach (str_split($password) as $c) {
            if (ctype_upper($c)) {
                $upperCaseCount += 1;
            }
            if ($upperCaseCount >= $this->minUpperCase) {
                return true;
            }
        }
        return false;
    }
}
