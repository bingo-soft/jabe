<?php

namespace Jabe\Impl\Db;

use Doctrine\DBAL\{Connection, DriverManager};
use MyBatis\DataSource\DataSourceInterface;

/** Keeps database connections process-local when JobExecutor forks. */
class ForkSafeDataSource implements DataSourceInterface
{
    private array $driverProperties = [];
    private ?Connection $connection = null;
    private int $connectionPid;
    private int $reconnectAttempts = 0;
    private int $reconnectDelay = 0;
    private bool $autoCommit = false;
    private $defaultTransactionIsolationLevel;

    public function __construct(
        private ?string $driver = null,
        private ?string $url = null,
        private ?string $username = null,
        private ?string $password = null
    ) {
        $this->connectionPid = getmypid();
    }

    public function setDriverProperties(array $driverProperties): void
    {
        $this->driverProperties = $driverProperties;
        $options = $driverProperties['driverOptions'] ?? [];
        $this->reconnectAttempts = (int) ($options['RECONNECT_ATTEMPTS'] ?? 0);
        $this->reconnectDelay = (int) ($options['RECONNECT_DELAY'] ?? 0);
        unset($this->driverProperties['driverOptions']['RECONNECT_ATTEMPTS']);
        unset($this->driverProperties['driverOptions']['RECONNECT_DELAY']);
    }

    public function getConnection(): Connection
    {
        $pid = getmypid();
        if ($this->connection === null || $this->connectionPid !== $pid) {
            // Never close the inherited object: it belongs to the parent process.
            $this->connection = null;
            $this->connectionPid = $pid;
            $props = array_merge([
                'driver' => $this->driver,
                'user' => $this->username,
                'password' => $this->password,
            ], $this->driverProperties);
            if (!empty($this->url)) {
                $props['url'] = $this->url;
            }
            $this->connection = DriverManager::getConnection($props);
        }

        try {
            $this->configureConnection($this->connection);
        } catch (\Throwable $e) {
            $this->reconnect();
        }

        return $this->connection;
    }

    private function configureConnection(Connection $connection): void
    {
        if ($this->autoCommit !== $connection->isAutoCommit()) {
            $connection->setAutoCommit($this->autoCommit);
        }
        if ($this->defaultTransactionIsolationLevel !== null) {
            $connection->setTransactionIsolation($this->defaultTransactionIsolationLevel);
        }
    }

    public function getReconnectAttempts(): int
    {
        return $this->reconnectAttempts;
    }

    public function reconnect(): void
    {
        $this->connection = null;
        if ($this->reconnectDelay > 0) {
            sleep($this->reconnectDelay);
        }
        $this->getConnection();
    }

    public function setDefaultTransactionIsolationLevel($level): void
    {
        $this->defaultTransactionIsolationLevel = $level;
    }
}
