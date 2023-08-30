<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetIdentityLinksForProcessDefinitionCmd implements CommandInterface
{
    protected $processDefinitionId;

    public function __construct(?string $processDefinitionId)
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function __serialize(): array
    {
        return [
            'processDefinitionId' => $this->processDefinitionId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processDefinitionId = $data['processDefinitionId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processDefinition = Context::getCommandContext()
            ->getProcessDefinitionManager()
            ->findLatestProcessDefinitionById($this->processDefinitionId);

        EnsureUtil::ensureNotNull("Cannot find process definition with id " . $this->processDefinitionId, "processDefinition", $processDefinition);

        $identityLinks = $processDefinition->getIdentityLinks();
        return $identityLinks;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
