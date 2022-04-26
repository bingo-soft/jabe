<?php

namespace Jabe\Engine\Impl\Util\El;

interface ELContextListener
{
    /**
     * Invoked when a new ELContext has been created.
     *
     * @param ece
     *            the notification event.
     */
    public function contextCreated(ELContextEvent $ece): void;
}
