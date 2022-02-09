<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

interface JobHandlerConfigurationInterface
{
    public function toCanonicalString(): string;
}
