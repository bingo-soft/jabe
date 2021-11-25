<?php

namespace BpmPlatform\Engine\Impl\Util\El;

class ELContextEvent
{
    protected $source;

    /**
     * Constructs an ELContextEvent object to indicate that an ELContext has been created.
     *
     * @param source
     *            the ELContext that was created.
     */
    public function __construct(ELContext $source)
    {
        $this->source = $source;
    }

    /**
     * Returns the ELContext that was created.
     *
     * @return the ELContext that was created.
     */
    public function getELContext(): ELContext
    {
        return $this->source;
    }
}
