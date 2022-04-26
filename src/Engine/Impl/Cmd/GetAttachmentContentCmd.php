<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Db\EntityManager\DbEntityManager;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    AttachmentEntity,
    ByteArrayEntity
};

class GetAttachmentContentCmd implements CommandInterface, \Serializable
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
        $dbEntityManger = $commandContext->getDbEntityManager();
        $attachment = $dbEntityManger->selectById(AttachmentEntity::class, $this->attachmentId);

        $contentId = $attachment->getContentId();
        if ($contentId == null) {
            return null;
        }

        $byteArray = $dbEntityManger->selectById(ByteArrayEntity::class, $contentId);
        $bytes = $byteArray->getBytes();

        return $bytes;
    }
}
