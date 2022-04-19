<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class GetIdentityLinksForProcessDefinitionCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionId;

    public function __construct(string $processDefinitionId)
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function serialize()
    {
        return json_encode([
            'processDefinitionId' => $this->processDefinitionId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processDefinitionId = $json->processDefinitionId;
    }

    public function execute(CommandContext $commandContext)
    {
        $processDefinition = Context::getCommandContext()
            ->getProcessDefinitionManager()
            ->findLatestProcessDefinitionById($this->processDefinitionId);

        EnsureUtil::ensureNotNull("Cannot find process definition with id " . $this->processDefinitionId, "processDefinition", $processDefinition);

        $identityLinks = $processDefinition->getIdentityLinks();
        return $identityLinks;
    }
}
