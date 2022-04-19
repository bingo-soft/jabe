<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class AddIdentityLinkForProcessDefinitionCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionId;

    protected $userId;

    protected $groupId;

    public function __construct(string $processDefinitionId, string $userId, string $groupId)
    {
        $this->validateParams($userId, $groupId, $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        $this->userId = $userId;
        $this->groupId = $groupId;
    }

    public function serialize()
    {
        return json_encode([
            'processDefinitionId' => $this->processDefinitionId,
            'userId' => $this->userId,
            'groupId' => $this->groupId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processDefinitionId = $json->processDefinitionId;
        $this->userId = $json->userId;
        $this->groupId = $json->groupId;
    }

    protected function validateParams(?string $userId, ?string $groupId, string $processDefinitionId): void
    {
        EnsureUtil::ensureNotNull("processDefinitionId", $processDefinitionId);

        if ($userId == null && $groupId == null) {
            throw new ProcessEngineException("userId and groupId cannot both be null");
        }
    }

    public function execute(CommandContext $commandContext)
    {
        $processDefinition = Context::getCommandContext()
            ->getProcessDefinitionManager()
            ->findLatestProcessDefinitionById($this->processDefinitionId);

        EnsureUtil::ensureNotNull("Cannot find process definition with id " . $this->processDefinitionId, "processDefinition", $processDefinition);

        $processDefinition->addIdentityLink($this->userId, $this->groupId);
        return null;
    }
}
