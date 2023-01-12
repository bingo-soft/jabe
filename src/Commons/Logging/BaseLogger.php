<?php

namespace Jabe\Commons\Logging;

abstract class BaseLogger
{
    protected $delegateLogger;

    /** the project code of the logger */
    protected $projectCode;

    /** the component Id of the logger. */
    protected $componentId;

    public static function createLogger($loggerClass, $projectCode, ?string $name, ?string $componentId): BaseLogger
    {
        try {
            $logger = new $loggerClass();
            $logger->projectCode = $projectCode;
            $logger->componentId = $componentId;
            $logger->delegateLogger = new ExtLogger($name);
            return $logger;
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Unable to instantiate logger '%s'", $loggerClass));
        }
    }

    /**
     * Logs a 'DEBUG' message
     *
     * @param id the unique id of this log message
     * @param messageTemplate the message template to use
     * @param parameters a list of optional parameters
     */
    protected function logDebug(?string $id, ?string $messageTemplate, ?array $parameters = null): void
    {
        if ($this->delegateLogger->isDebugEnabled()) {
            $msg = $this->formatMessageTemplate($id, $messageTemplate);
            $this->delegateLogger->debug($msg, $parameters);
        }
    }

    /**
     * Logs an 'INFO' message
     *
     * @param id the unique id of this log message
     * @param messageTemplate the message template to use
     * @param parameters a list of optional parameters
     */
    protected function logInfo(?string $id, ?string $messageTemplate, ?array $parameters = null): void
    {
        if ($this->delegateLogger->isInfoEnabled()) {
            $msg = $this->formatMessageTemplate($id, $messageTemplate);
            $this->delegateLogger->info($msg, $parameters);
        }
    }

    /**
     * Logs an 'WARN' message
     *
     * @param id the unique id of this log message
     * @param messageTemplate the message template to use
     * @param parameters a list of optional parameters
     */
    protected function logWarn(?string $id, ?string $messageTemplate, ?array $parameters = null): void
    {
        if ($this->delegateLogger->isWarnEnabled()) {
            $msg = $this->formatMessageTemplate($id, $messageTemplate);
            $this->delegateLogger->warn($msg, $parameters);
        }
    }

    /**
     * Logs an 'ERROR' message
     *
     * @param id the unique id of this log message
     * @param messageTemplate the message template to use
     * @param parameters a list of optional parameters
     */
    protected function logError(?string $id, ?string $messageTemplate, ?array $parameters = null): void
    {
        if ($this->delegateLogger->isErrorEnabled()) {
            $msg = $this->formatMessageTemplate($id, $messageTemplate);
            $this->delegateLogger->error($msg, $parameters);
        }
    }

    public function setLevel(int $logLevel): void
    {
        $this->delegateLogger->setLevel($logLevel);
    }

    /**
     * @return bool - true if the logger will log 'DEBUG' messages
     */
    public function isDebugEnabled(): bool
    {
        return $this->delegateLogger->isDebugEnabled();
    }

    /**
     * @return bool - true if the logger will log 'INFO' messages
     */
    public function isInfoEnabled(): bool
    {
        return $this->delegateLogger->isInfoEnabled();
    }

    /**
     * @return bool - true if the logger will log 'WARN' messages
     */
    public function isWarnEnabled(): bool
    {
        return $this->delegateLogger->isWarnEnabled();
    }

    /**
     * @return bool - true if the logger will log 'ERROR' messages
     */
    public function isErrorEnabled(): bool
    {
        return $this->delegateLogger->isErrorEnabled();
    }

    /**
     * Formats a message template
     *
     * @param id the id of the message
     * @param messageTemplate the message template to use
     *
     * @return string the formatted template
     */
    protected function formatMessageTemplate(?string $id, ?string $messageTemplate): ?string
    {
        return sprintf("%s-%s%s %s", $this->projectCode, $this->componentId, $id, $messageTemplate);
    }

    /**
     * Prepares an exception message
     *
     * @param id the id of the message
     * @param messageTemplate the message template to use
     * @param parameters the parameters for the message (optional)
     *
     * @return string the prepared exception message
     */
    protected function exceptionMessage(?string $id, ?string $messageTemplate, ?array $parameters = null): ?string
    {
        $formattedTemplate = $this->formatMessageTemplate($id, $messageTemplate);
        if (empty($parameters)) {
            return $formattedTemplate;
        } else {
            return sprintf($formattedTemplate, ...$parameters);
        }
    }
}
