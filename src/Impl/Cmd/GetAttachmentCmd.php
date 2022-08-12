<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\AttachmentEntity;

class GetAttachmentCmd implements CommandInterface, \Serializable
{
    protected $attachmentId;

    public function __construct(string $attachmentId)
    {
        $this->attachmentId = $attachmentId;
    }

    public function serialize()
    {
        return json_encode([
            'attachmentId' => $this->attachmentId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->attachmentId = $json->attachmentId;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getDbEntityManager()
            ->selectById(AttachmentEntity::class, $this->attachmentId);
    }
}
