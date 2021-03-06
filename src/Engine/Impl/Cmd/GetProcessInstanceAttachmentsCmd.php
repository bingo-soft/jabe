<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetProcessInstanceAttachmentsCmd implements CommandInterface, \Serializable
{
    protected $processInstanceId;

    public function __construct(string $taskId)
    {
        $this->processInstanceId = $taskId;
    }

    public function serialize()
    {
        return json_encode([
            'processInstanceId' => $this->processInstanceId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processInstanceId = $json->processInstanceId;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getAttachmentManager()
            ->findAttachmentsByProcessInstanceId($this->processInstanceId);
    }
}
