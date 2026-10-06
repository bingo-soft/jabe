<?php

namespace Tests\Impl\JobExecutor;

use Jabe\Impl\Cfg\StandaloneProcessEngineConfiguration;
use Jabe\Impl\JobExecutor\DefaultJobExecutor;
use PHPUnit\Framework\TestCase;

class SharedQueuePostgresIntegrationTest extends TestCase
{
    private const TIMER_PROCESS = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL" targetNamespace="Examples">
  <process id="sharedQueueTimer" isExecutable="true">
    <startEvent id="start"/>
    <sequenceFlow id="toTimer" sourceRef="start" targetRef="timer"/>
    <intermediateCatchEvent id="timer">
      <timerEventDefinition><timeDuration>PT1S</timeDuration></timerEventDefinition>
    </intermediateCatchEvent>
    <sequenceFlow id="toEnd" sourceRef="timer" targetRef="end"/>
    <endEvent id="end"/>
  </process>
</definitions>
XML;

    private function requireDisposablePostgres(): void
    {
        if (getenv('DB_JABE_DRIVER') !== 'pdo_pgsql'
            || getenv('JABE_TEST_DISPOSABLE_DB') !== '1'
            || !str_starts_with((string) getenv('DB_JABE_NAME'), 'jabe_test')) {
            $this->markTestSkipped('Requires an explicitly marked disposable jabe_test PostgreSQL database');
        }
    }

    public function testAutomaticAcquisitionExecutesMultipleTimerJobs(): void
    {
        $this->requireDisposablePostgres();

        $configuration = new StandaloneProcessEngineConfiguration();
        if (getenv('JABE_TEST_SINGLE_WORKER') === '1') {
            $executor = new DefaultJobExecutor(...$configuration->getJobExecutorState());
            $executor->setCorePoolSize(1);
            $executor->setMaxPoolSize(1);
            $configuration->setJobExecutor($executor);
        }
        $configuration->setJobExecutorActivate(true);
        $configuration->setResource('tests/Resources/engine.cfg.xml');
        $engine = $configuration->buildProcessEngine();
        $engine->getRepositoryService()->createDeployment()
            ->addString('sharedQueueTimer.bpmn20.xml', self::TIMER_PROCESS)->deploy();

        $instanceIds = [];
        for ($i = 0; $i < 10; ++$i) {
            $instanceIds[] = $engine->getRuntimeService()->startProcessInstanceByKey('sharedQueueTimer')->getId();
        }

        $deadline = microtime(true) + 20;
        do {
            $remaining = 0;
            foreach ($instanceIds as $id) {
                $remaining += $engine->getRuntimeService()->createProcessInstanceQuery()
                    ->processInstanceId($id)->count();
            }
            if ($remaining === 0) {
                break;
            }
            usleep(200000);
        } while (microtime(true) < $deadline);

        $this->assertSame(0, $remaining, 'Automatic acquisition did not complete all timer jobs');
        $this->assertFalse($configuration->getJobExecutor()->getThreadPoolExecutor()->isFailed());

        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            getenv('DB_JABE_HOST'),
            getenv('DB_JABE_PORT') ?: 5432,
            getenv('DB_JABE_NAME')
        );
        $db = new \PDO($dsn, getenv('DB_JABE_USER'), getenv('DB_JABE_PASSWORD'));
        $placeholders = implode(',', array_fill(0, count($instanceIds), '?'));
        $statement = $db->prepare(
            "select job_state_, count(*) from act_hi_job_log where process_instance_id_ in ($placeholders) group by job_state_"
        );
        $statement->execute($instanceIds);
        $states = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
        $this->assertSame(10, (int) ($states[2] ?? 0), 'Not every timer job completed successfully');
        $this->assertSame(0, (int) ($states[1] ?? 0), 'Timer jobs were retried after an unexpected failure');
    }
}
