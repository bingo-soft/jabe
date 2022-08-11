<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Commons\Logging\MdcAccess;

class ProcessDataStack
{
    protected $mdcName;
    protected $deque = [];

    private const NULL_VALUE = "~NULL_VALUE~";

    /**
     * @param mdcName is optional. If present, any additions to a stack will also be reflected in the MDC context
     */
    public function __construct(string $mdcName)
    {
        $this->mdcName = $mdcName;
    }

    public function isEmpty(): bool
    {
        return empty($this->deque);
    }

    public function getCurrentValue(): ?string
    {
        if (!empty($this->deque)) {
            return $this->deque[0];
        }
        return null;
    }

    public function pushCurrentValue(?string $value): void
    {
        array_unshift($this->deque, $value ?? self::NULL_VALUE);

        $this->updateMdcWithCurrentValue();
    }

    /**
     * @return bool true if a value was obtained from the mdc
     *   and added to the stack
     */
    public function pushCurrentValueFromMdc(): bool
    {
        if ($this->isNotBlank($this->mdcName)) {
            $mdcValue = MdcAccess::get($this->mdcName);

            array_unshift($this->deque, $mdcValue ?? self::NULL_VALUE);
            return true;
        } else {
            return false;
        }
    }

    public function removeCurrentValue(): void
    {
        array_shift($this->deque);

        $this->updateMdcWithCurrentValue();
    }

    public function clearMdcProperty(): void
    {
        if ($this->isNotBlank($this->mdcName)) {
            MdcAccess::remove($this->mdcName);
        }
    }

    public function updateMdcWithCurrentValue(): void
    {
        if ($this->isNotBlank($this->mdcName)) {
            $currentValue = $this->getCurrentValue();

            if ($currentValue === null || $currentValue == self::NULL_VALUE) {
                MdcAccess::remove($this->mdcName);
            } else {
                MdcAccess::put($this->mdcName, $currentValue);
            }
        }
    }
}
