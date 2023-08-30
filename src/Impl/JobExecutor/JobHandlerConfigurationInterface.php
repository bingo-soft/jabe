<?php

namespace Jabe\Impl\JobExecutor;

interface JobHandlerConfigurationInterface
{
    public function toCanonicalString(): ?string;
}
