<?php

namespace BpmPlatform\Engine\Impl\Expression;

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
