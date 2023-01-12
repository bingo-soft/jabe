<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTaskAttachmentCmd implements CommandInterface, \Serializable
{
    protected $attachmentId;
    protected $taskId;

    public function __construct(?string $taskId, ?string $attachmentId)
    {
        $this->attachmentId = $attachmentId;
        $this->taskId = $taskId;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'attachmentId' => $this->attachmentId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->attachmentId = $json->attachmentId;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getAttachmentManager()
            ->findAttachmentByTaskIdAndAttachmentId($this->taskId, $this->attachmentId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
