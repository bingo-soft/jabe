<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Db\IdBlock;
use Jabe\Impl\Interceptor\{
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
