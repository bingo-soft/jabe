<?php

namespace BpmPlatform\Engine\Impl\Identity;

use BpmPlatform\Engine\Identity\{
    PasswordPolicyRuleInterface,
    UserInterface
};

class PasswordPolicyUserDataRuleImpl implements PasswordPolicyRuleInterface
{
    public const PLACEHOLDER = DefaultPasswordPolicyImpl::PLACEHOLDER_PREFIX . "USER_DATA";

    public function getPlaceholder(): string
    {
        return PasswordPolicyUserDataRuleImpl::PLACEHOLDER;
    }

    public function getParameters(): array
    {
        return [];
    }

    public function execute(string $candidatePassword, ?UserInterface $user = null): bool
    {
        if (empty($candidatePassword) || $user == null) {
            return true;
        } else {
            $candidatePassword = $this->upperCase($candidatePassword);

            $id = $this->upperCase($user->getId());
            $firstName = $this->upperCase($user->getFirstName());
            $lastName = $this->upperCase($user->getLastName());
            $email = $this->upperCase($user->getEmail());

            return !($this->isNotBlank($id) && strpos($candidatePassword, $id) !== false ||
            $this->isNotBlank($firstName) && strpos($candidatePassword, $firstName) !== false ||
            $this->isNotBlank($lastName) && strpos($candidatePassword, $lastName) !== false ||
            $this->isNotBlank($email) && strpos($candidatePassword, $email) !== false);
        }
    }

    public function upperCase(?string $string): ?string
    {
        return $string == null ? null : strtoupper($string);
    }

    public function isNotBlank(?string $value): bool
    {
        return !empty($value);
    }
}
