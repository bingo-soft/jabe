<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CheckPassword implements CommandInterface, \Serializable
{
    private $userId;
    private $password;

    public function __construct(string $userId, string $password)
    {
        $this->userId = $userId;
        $this->password = $password;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId,
            'password' => $this->password
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
        $this->password = $json->password;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getReadOnlyIdentityProvider()->checkPassword($this->userId, $this->password);
    }
}
