<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Db\IdBlock;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetNextIdBlockCmd implements CommandInterface
{
    protected $idBlockSize;

    public function __construct(int $idBlockSize)
    {
        $this->idBlockSize = $idBlockSize;
    }

    public function execute(CommandContext $commandContext)
    {
        $property = $commandContext
            ->getPropertyManager()
            ->findPropertyById("next.dbid");
        $oldValue = intval($property->getValue());
        $newValue = $oldValue + $this->idBlockSize;
        $property->setValue(strval($newValue));
        return new IdBlock($oldValue, $newValue - 1);
    }
}
