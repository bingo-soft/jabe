<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetFilterCmd implements CommandInterface, \Serializable
{
    protected $filterId;

    public function __construct(string $filterId)
    {
        $this->filterId = $filterId;
    }

    public function serialize()
    {
        return json_encode([
            'filterId' => $this->filterId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->filterId = $json->filterId;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getFilterManager()
            ->findFilterById($this->filterId);
    }
}
