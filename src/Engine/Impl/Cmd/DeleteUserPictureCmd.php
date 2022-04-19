<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\IdentityInfoEntity;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class DeleteUserPictureCmd implements CommandInterface
{
    protected $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("UserId", "UserId", $this->userId);

        $infoEntity = $commandContext->getIdentityInfoManager()
            ->findUserInfoByUserIdAndKey($this->userId, "picture");

        if ($infoEntity != null) {
            $byteArrayId = $infoEntity->getValue();
            if ($byteArrayId != null) {
                $commandContext->getByteArrayManager()
                    ->deleteByteArrayById($byteArrayId);
            }
            $commandContext->getIdentityInfoManager()
            ->delete($infoEntity);
        }

        return null;
    }
}
