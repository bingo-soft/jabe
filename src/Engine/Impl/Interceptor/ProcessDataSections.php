<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

class ProcessDataSections
{
    /**
     * Keeps track of when we added values to which stack (as we do not add
     * a new value to every stack with every update, but only changed values)
     */
    protected $sections = [];

    protected $currentSectionSealed = true;

    /**
     * Adds a stack to the current section. If the current section is already sealed,
     * a new section is created.
     */
    public function addToCurrentSection(ProcessDataStack $stack): void
    {
        $currentSection = [];

        if ($this->currentSectionSealed) {
            $currentSection = [$stack];
            array_unshift($this->sections, $currentSection);
            $this->currentSectionSealed = false;
        } else {
            $this->sections[0][] = $stack;
        }
    }

    /**
     * Pops the current section and removes the
     * current values from the referenced stacks (including updates
     * to the MDC)
     */
    public function popCurrentSection(): void
    {
        $section = array_shift($this->sections);
        if ($section != null) {
            foreach ($section as $stack) {
                $stack->removeCurrentValue();
            }
        }

        $this->currentSectionSealed = true;
    }

    /**
     * After a section is sealed, a new section will be created
     * with the next call to {@link #addToCurrentSection(ProcessDataStack)}
     */
    public function sealCurrentSection(): void
    {
        $this->currentSectionSealed = true;
    }

    public function size(): int
    {
        return count($this->sections);
    }
}
