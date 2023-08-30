<?php

namespace Jabe\Impl\Identity;

use Jabe\Identity\{
    PasswordPolicyInterface,
    PasswordPolicyRuleInterface
};

class DefaultPasswordPolicyImpl
{
    protected const PLACEHOLDER_PREFIX = "PASSWORD_POLICY_";

    // password length
    public const MIN_LENGTH = 10;
    // password complexity
    public const MIN_LOWERCASE = 1;
    public const MIN_UPPERCASE = 1;
    public const MIN_DIGIT = 1;
    public const MIN_SPECIAL = 1;

    protected $rules = [];

    public function __construct()
    {
        $this->rules[] = new PasswordPolicyUserDataRuleImpl();
        $this->rules[] = new PasswordPolicyLengthRuleImpl(self::MIN_LENGTH);
        $this->rules[] = new PasswordPolicyLowerCaseRuleImpl(self::MIN_LOWERCASE);
        $this->rules[] = new PasswordPolicyUpperCaseRuleImpl(self::MIN_UPPERCASE);
        $this->rules[] = new PasswordPolicyDigitRuleImpl(self::MIN_DIGIT);
        $this->rules[] = new PasswordPolicySpecialCharacterRuleImpl(self::MIN_SPECIAL);
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
