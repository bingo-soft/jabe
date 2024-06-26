<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class AddIdentityLinkForProcessDefinitionCmd implements CommandInterface
{
    protected $processDefinitionId;

    protected $userId;

    protected $groupId;

    public function __construct(?string $processDefinitionId, ?string $userId, ?string $groupId)
    {
        $this->validateParams($userId, $groupId, $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        $this->userId = $userId;
        $this->groupId = $groupId;
    }

    public function __serialize(): array
    {
        return [
            'processDefinitionId' => $this->processDefinitionId,
            'userId' => $this->userId,
            'groupId' => $this->groupId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->userId = $data['userId'];
        $this->groupId = $data['groupId'];
    }

    protected function validateParams(?string $userId, ?string $groupId, ?string $processDefinitionId): void
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);

        if ($userId === null && $groupId === null) {
            throw new ProcessEngineException("userId and groupId cannot both be null");
        }
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processDefinition = Context::getCommandContext()
            ->getProcessDefinitionManager()
            ->findLatestProcessDefinitionById($this->processDefinitionId);

        EnsureUtil::ensureNotNull("Cannot find process definition with id " . $this->processDefinitionId, "processDefinition", $processDefinition);

        $processDefinition->addIdentityLink($this->userId, $this->groupId);
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
