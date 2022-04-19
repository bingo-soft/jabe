<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Identity\UserInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\UserEntity;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SaveUserCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $user;
    protected $skipPasswordPolicy;

    public function __construct(UserInterface $user, bool $skipPasswordPolicy = false)
    {
        $this->user = $user;
        $this->skipPasswordPolicy = $skipPasswordPolicy;
    }

    public function serialize()
    {
        return json_encode([
            'user' => serialize($this->user),
            'skipPasswordPolicy' => $this->skipPasswordPolicy
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->user = unserialize($json->user);
        $this->skipPasswordPolicy = $json->skipPasswordPolicy;
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("user", "user", $this->user);
        EnsureUtil::ensureWhitelistedResourceId($commandContext, "User", $this->user->getId());

        if ($user instanceof UserEntity) {
            $this->validateUserEntity($commandContext);
        }

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->saveUser($user);

        $commandContext->getOperationLogManager()->logUserOperation($operationResult, $this->user->getId());

        return null;
    }

    private function validateUserEntity(CommandContext $commandContext): void
    {
        if ($this->shouldCheckPasswordPolicy($commandContext)) {
            if (!($this->user->checkPasswordAgainstPolicy())) {
                throw new ProcessEngineException("Password does not match policy");
            }
        }
    }

    protected function shouldCheckPasswordPolicy(CommandContext $commandContext): bool
    {
        return $this->user->hasNewPassword() && !$this->skipPasswordPolicy
            && $commandContext->getProcessEngineConfiguration()->isEnablePasswordPolicy();
    }
}
