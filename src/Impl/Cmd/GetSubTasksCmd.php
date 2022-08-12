<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\TaskQueryImpl;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetSubTasksCmd implements CommandInterface, \Serializable
{
    protected $parentTaskId;

    public function __construct(string $parentTaskId)
    {
        $this->parentTaskId = $parentTaskId;
    }

    public function serialize()
    {
        return json_encode([
            'parentTaskId' => $this->parentTaskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->parentTaskId = $json->parentTaskId;
    }

    public function execute(CommandContext $commandContext)
    {
        return (new TaskQueryImpl())
            ->taskParentTaskId($this->parentTaskId)
            ->list();
    }
}
