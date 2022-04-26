<?php

namespace Jabe\Engine\Impl\JobExecutor;

interface JobHandlerConfigurationInterface
{
    public function toCanonicalString(): string;
}
