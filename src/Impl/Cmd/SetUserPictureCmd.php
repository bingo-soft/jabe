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
use Jabe\Repository\ResourceTypes;

class SetUserPictureCmd implements CommandInterface
{
    protected $userId;
    protected $picture;

    public function __construct(?string $userId, Picture $picture)
    {
        $this->userId = $userId;
        $this->picture = $picture;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId,
            'picture' => serialize($this->picture)
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
        $this->picture = unserialize($data['picture']);
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("userId", "userId", $this->userId);

        $pictureInfo = $commandContext->getIdentityInfoManager()
            ->findUserInfoByUserIdAndKey($this->userId, "picture");

        if ($pictureInfo !== null) {
            $byteArrayId = $pictureInfo->getValue();
            if ($byteArrayId !== null) {
                $commandContext->getByteArrayManager()
                    ->deleteByteArrayById($byteArrayId);
            }
        } else {
            $pictureInfo = new IdentityInfoEntity();
            $pictureInfo->setUserId($this->userId);
            $pictureInfo->setKey("picture");
            $commandContext->getDbEntityManager()->insert($pictureInfo, ...$args);
        }

        $byteArrayEntity = new ByteArrayEntity($this->picture->getMimeType(), $this->picture->getBytes(), ResourceTypes::repository());

        $commandContext->getByteArrayManager()
            ->insertByteArray($byteArrayEntity);

        $pictureInfo->setValue($byteArrayEntity->getId());

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
