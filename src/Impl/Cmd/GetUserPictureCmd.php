<?php

namespace Jabe\Impl\Cmd;

use Jabe\Identity\Picture;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    IdentityInfoEntity
};
use Jabe\Impl\Util\EnsureUtil;

class GetUserPictureCmd implements CommandInterface, \Serializable
{
    protected $userId;

    public function __construct(?string $userId)
    {
        $this->userId = $userId;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("userId", "userId", $this->userId);

        $pictureInfo = $commandContext->getIdentityInfoManager()
            ->findUserInfoByUserIdAndKey($this->userId, "picture");

        if ($pictureInfo !== null) {
            $pictureByteArrayId = $pictureInfo->getValue();
            if ($pictureByteArrayId !== null) {
                $byteArray = $commandContext->getDbEntityManager()
                    ->selectById(ByteArrayEntity::class, $pictureByteArrayId);
                return new Picture($byteArray->getBytes(), $byteArray->getName());
            }
        }

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
