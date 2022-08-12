<?php

namespace Jabe\Impl\Identity;

use Jabe\Identity\PasswordPolicyRuleInterface;

class PasswordPolicySpecialCharacterRuleImpl implements PasswordPolicyRuleInterface
{
    public const SPECIALCHARACTERS = " !\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~";
    public const PLACEHOLDER = DefaultPasswordPolicyImpl::PLACEHOLDER_PREFIX . "SPECIAL";

    protected $minSpecial;

    public function __construct(int $minSpecial)
    {
        $this->minSpecial = $minSpecial;
    }

    public function getPlaceholder(): string
    {
        return PasswordPolicySpecialCharacterRuleImpl::PLACEHOLDER;
    }

    public function getParameters(): array
    {
        $parameter = [];
        $parameter["minSpecial"] = $this->minSpecial;
        return $parameter;
    }

    public function execute(string $password): bool
    {
        $specialCount = 0;
        foreach (str_split($password) as $c) {
            if (strpos(self::SPECIALCHARACTERS, $c) !== false) {
                $specialCount += 1;
            }
            if ($specialCount >= $this->minSpecial) {
                return true;
            }
        }
        return false;
    }
}
