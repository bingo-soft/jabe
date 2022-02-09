<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

interface RunnableInterface
{
    public function run(): void;
}
