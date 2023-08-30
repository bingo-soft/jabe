<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateAuthorizationCommand implements CommandInterface
{
    protected int $type = 0;

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext->getAuthorizationManager()->createNewAuthorization($this->type);
    }
}
