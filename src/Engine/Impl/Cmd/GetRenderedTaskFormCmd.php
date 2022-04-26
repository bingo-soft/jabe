<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetRenderedTaskFormCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $formEngineName;

    public function __construct(string $taskId, string $formEngineName)
    {
        $this->taskId = $taskId;
        $this->formEngineName = $formEngineName;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'formEngineName' => $this->formEngineName
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->formEngineName = $json->formEngineName;
    }

    public function execute(CommandContext $commandContext)
    {
        $taskManager = $commandContext->getTaskManager();
        $task = $taskManager->findTaskById($taskId);
        EnsureUtil::ensureNotNull("Task '" . $this->taskId . "' not found", "task", $task);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTaskVariable($task);
        }
        EnsureUtil::ensureNotNull("Task form definition for '" . $this->taskId . "' not found", "task.getTaskDefinition()", $task->getTaskDefinition());

        $taskFormHandler = $task->getTaskDefinition()->getTaskFormHandler();
        if ($taskFormHandler == null) {
            return null;
        }

        $formEngines = Context::getProcessEngineConfiguration()
            ->getFormEngines();
        $formEngine = null;
        if (array_key_exists($this->formEngineName, $formEngines)) {
            $formEngine = $formEngines[$this->formEngineName];
        }

        EnsureUtil::ensureNotNull("No formEngine '" . $this->formEngineName . "' defined process engine configuration", "formEngine", $formEngine);

        $taskForm = $taskFormHandler->createTaskForm($task);

        return $formEngine->renderTaskForm($taskForm);
    }
}
