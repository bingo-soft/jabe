<?php

namespace Jabe\Impl\Identity;

use Jabe\Identity\PasswordPolicyRuleInterface;

class PasswordPolicyLowerCaseRuleImpl implements PasswordPolicyRuleInterface
{
    public const PLACEHOLDER = DefaultPasswordPolicyImpl::PLACEHOLDER_PREFIX . "LOWERCASE";

    protected $minLowerCase;

    public function __construct(int $minLowerCase)
    {
        $this->minLowerCase = $minLowerCase;
    }

    public function getPlaceholder(): string
    {
        return PasswordPolicyLowerCaseRuleImpl::PLACEHOLDER;
    }

    public function getParameters(): array
    {
        $parameter = [];
        $parameter["minLowerCase"] = $this->minLowerCase;
        return $parameter;
    }

    public function execute(string $password): bool
    {
        $lowerCaseCount = 0;
        foreach (str_split($password) as $c) {
            if (ctype_lower($c)) {
                $lowerCaseCount += 1;
            }
            if ($lowerCaseCount >= $this->minLowerCase) {
                return true;
            }
        }
        return false;
    }
}
