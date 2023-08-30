<?php

namespace EngineStarterService\Service;

use Phalcon\Di\Di;
use RdKafka\Conf;
use RdKafka\TopicConf;
use RdKafka\{
    KafkaConsumer,
    Producer
};

class Engine
{
    private $processEngine;
    private const GROUP_ID = 'engine_consumer';
    private const AUTO_COMMIT_ENABLE = 'false';
    private const ENABLE_PARTITION_EOF = 'true';
    private const METADATA_MAX_AGE_MS = 5000;
    private const AUTO_OFFSET_RESET = 'earliest';
    private const CONSUME_TIMEOUT_MS = 5000;
    private const DATABASE_RESET_TIMEOUT = 5;
    private const RECONNECT_TIMEOUT = 30000;
    private const RECONNECT_TIMEOUT_MAX = 60000;
    private const STATISTICS = 30000;
    private const WORKER_DELAY = 30;

    private $di;
    private $brokerList;
    private $topics;
    private $taskTopic;

    private $kafkaConfig;
    private $consumer;
    private $producer;
    
    public function __construct(string $brokerList, array $topics)
    {
        $this->di = Di::getDefault();        
        $this->brokerList = $brokerList;
        $this->topics = $topics;
    }
    
    public function start(): void
    {
        fwrite(STDOUT, "==== BPMN Engine started ====\n");        
        $this->initKafka();        
        $this->startKafkaConsumer(); 
    }

    public function checkKafkaState(KafkaConsumer $kafka, string $info, int $infoLen): void
    {
        $info = json_decode($info, true);
        $info = array_shift($info['brokers']);

        if ($info['state'] != 'UP') {
            fwrite(STDERR, sprintf(
                "[%s] State Kafka %s: %s\n",
                date("d-m-Y H:i:s"),
                $info['name'],
                $info['state']
            ));
        }
    }

    public function logKafkaError(KafkaConsumer $kafka, int $error, string $reason): void
    {
        fwrite(STDERR, sprintf(
            "[%s] Kafka error: %s (reason: %s)\n",
            date("d-m-Y H:i:s"),
            rd_kafka_err2str($error),
            $reason
        ));
    }

    public function logKafkaInfo(KafkaConsumer $kafka, int $level, string $facility, string $message): void
    {
        fwrite(STDERR, sprintf(
            "[%s] Kafka %s: %s (level: %d)\n",
            date("d-m-Y H:i:s"),
            $facility,
            $message,
            $level
        ));
    }

    private function initKafka(): void
    {
        $topicConf = new TopicConf();
        $topicConf->set('auto.offset.reset', self::AUTO_OFFSET_RESET);

        $this->config = new Conf();
        $this->config->setDefaultTopicConf($topicConf);
        $this->config->set('group.id', self::GROUP_ID);
        $this->config->set('enable.auto.commit', self::AUTO_COMMIT_ENABLE);
        $this->config->set('enable.partition.eof', self::ENABLE_PARTITION_EOF);
        $this->config->set('metadata.broker.list', $this->brokerList);
        $this->config->set('metadata.max.age.ms', self::METADATA_MAX_AGE_MS);
        $this->config->set('reconnect.backoff.ms', self::RECONNECT_TIMEOUT);
        $this->config->set('reconnect.backoff.max.ms', self::RECONNECT_TIMEOUT_MAX);
        $this->config->set('statistics.interval.ms', self::STATISTICS);

        $this->config->setLogCb([$this, 'logKafkaInfo']);
        $this->config->setErrorCb([$this, 'logKafkaError']);
        $this->config->setStatsCb([$this, 'checkKafkaState']);

        $this->consumer = new KafkaConsumer($this->config);
    }

    private function startKafkaConsumer(): void
    {
        $this->consumer->subscribe($this->topics);
        while (true) {
            $message = $this->consumer->consume(self::CONSUME_TIMEOUT_MS);
            var_dump("wait for message");
            if (is_null($message) || $message->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
                continue;
            } elseif ($message->err === RD_KAFKA_RESP_ERR__TIMED_OUT) {
                continue;
            } elseif (!is_null($message->payload)) {
                $this->handleEvent($message);
            }
        }
    }

    private function handleEvent($message): void
    {
        try {
            if ($message->offset >= 0) {
                $event = json_decode($message->payload);
                if ($event !== null) {
                    if (property_exists($event, 'command')) {
                        var_dump($event);
                    }
                }
                $this->consumer->commit($message);
            }
        } catch (\Throwable $ex) {
            fwrite(STDERR, "BPMN Engine. Unknown exception occurred: " . $ex->getMessage() . ", " . $ex->getTraceAsString() . "\n");
        }
    }
}
