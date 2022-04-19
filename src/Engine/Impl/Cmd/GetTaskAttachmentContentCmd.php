<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\ByteArrayEntity;

class GetTaskAttachmentContentCmd implements CommandInterface, \Serializable
{
    protected $attachmentId;
    protected $taskId;

    public function __construct(string $taskId, string $attachmentId)
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
        $attachment = $commandContext
            ->getAttachmentManager()
            ->findAttachmentByTaskIdAndAttachmentId($this->taskId, $this->attachmentId);

        if ($attachment == null) {
            return null;
        }

        $contentId = $attachment->getContentId();
        if ($contentId == null) {
            return null;
        }

        $byteArray = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $contentId);

        $bytes = $byteArray->getBytes();

        return $bytes;
    }
}
