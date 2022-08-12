<?php

namespace Jabe\Impl\Runtime;

use Jabe\Impl\Interceptor\CommandContext;

interface CorrelationHandlerInterface
{
    /**
     * Correlate the given message to either a waiting execution or a process
     * definition with a message start event.
     *
     * @param correlationSet
     *          any of its members may be <code>null</code>
     *
     * @return CorrelationHandlerResult the matched correlation target or <code>null</code> if the message
     *         could not be correlated.
     */
    public function correlateMessage(CommandContext $commandContext, string $messageName, CorrelationSet $correlationSet): CorrelationHandlerResult;

    /**
     * Correlate the given message to all waiting executions and all process
     * definitions which have a message start event.
     *
     * @param correlationSet
     *          any of its members may be <code>null</code>
     *
     * @return all matched correlation targets or an empty List if the message
     *         could not be correlated.
     */
    public function correlateMessages(CommandContext $commandContext, string $messageName, CorrelationSet $correlationSet): array;

    /**
     * Correlate the given message to process definitions with a message start
     * event.
     *
     * @param correlationSet
     *          any of its members may be <code>null</code>
     *
     * @return array the matched correlation targets or an empty list if the message
     *         could not be correlated.
     */
    public function correlateStartMessages(CommandContext $commandContext, string $messageName, CorrelationSet $correlationSet): array;
}
