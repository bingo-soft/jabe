<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Identity\Picture;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    IdentityInfoEntity
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Repository\ResourceTypes;

class SetUserPictureCmd implements CommandInterface, \Serializable
{
    protected $userId;
    protected $picture;

    public function __construct(string $userId, Picture $picture)
    {
        $this->userId = $userId;
        $this->picture = $picture;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId,
            'picture' => serialize($this->picture)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
        $this->picture = unserialize($json->picture);
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("userId", "userId", $this->userId);

        $pictureInfo = $commandContext->getIdentityInfoManager()
            ->findUserInfoByUserIdAndKey($this->userId, "picture");

        if ($pictureInfo != null) {
            $byteArrayId = $pictureInfo->getValue();
            if ($byteArrayId != null) {
                $commandContext->getByteArrayManager()
                    ->deleteByteArrayById($byteArrayId);
            }
        } else {
            $pictureInfo = new IdentityInfoEntity();
            $pictureInfo->setUserId($this->userId);
            $pictureInfo->setKey("picture");
            $commandContext->getDbEntityManager()->insert($pictureInfo);
        }

        $byteArrayEntity = new ByteArrayEntity($picture->getMimeType(), $picture->getBytes(), ResourceTypes::repository());

        $commandContext->getByteArrayManager()
            ->insertByteArray($byteArrayEntity);

        $pictureInfo->setValue($byteArrayEntity->getId());

        return null;
    }
}
