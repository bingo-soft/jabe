<?php

namespace Jabe\Impl\Identity;

use Jabe\Identity\{
    PasswordPolicyResultInterface,
    PasswordPolicyRuleInterface
};

class PasswordPolicyResultImpl implements PasswordPolicyResultInterface
{
    protected $violatedRules;
    protected $fulfilledRules;

    public function __construct(array $violatedRules, array $fulfilledRules)
    {
        $this->violatedRules = $violatedRules;
        $this->fulfilledRules = $fulfilledRules;
    }

    public function isValid(): bool
    {
        return $this->violatedRules === null || count($this->violatedRules) == 0;
    }

    public function getViolatedRules(): array
    {
        return $this->violatedRules;
    }

    public function setViolatedRules(array $violatedRules): void
    {
        $this->violatedRules = $violatedRules;
    }

    public function getFulfilledRules(): array
    {
        return $this->fulfilledRules;
    }

    public function setFulfilledRules(array $fulfilledRules): void
    {
        $this->fulfilledRules = $fulfilledRules;
    }
}
