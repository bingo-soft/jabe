<?php

namespace Jabe\Identity;

interface PasswordPolicyRuleInterface
{
    public function getPlaceholder(): string;

    public function getParameters(): array;

    public function execute(string $candidatePassword, ?UserInterface $user): bool;
}
