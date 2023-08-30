<?php

namespace Jabe\Commons\Logging;

use Monolog\Logger;

class ExtLogger extends Logger
{
    private $currentLogLevel = self::INFO;

    public function setLevel(int $currentLogLevel): void
    {
        $this->currentLogLevel = $currentLogLevel;
    }

    public function getLevel(): int
    {
        return $this->currentLogLevel;
    }

    protected function isLevelEnabled(int $logLevel): bool
    {
        return $logLevel >= $this->currentLogLevel;
    }

    public function isDebugEnabled(): bool
    {

        return $this->isLevelEnabled(self::DEBUG);
    }

    public function isInfoEnabled(): bool
    {

        return $this->isLevelEnabled(self::INFO);
    }

    public function isWarnEnabled(): bool
    {

        return $this->isLevelEnabled(self::WARNING);
    }

    public function isErrorEnabled(): bool
    {

        return $this->isLevelEnabled(self::ERROR);
    }
}
