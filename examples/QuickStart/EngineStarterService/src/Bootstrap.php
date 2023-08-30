<?php

namespace EngineStarterService;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\Context\Context;
use Phalcon\Cli\Console;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Exception as PhalconException;

class Bootstrap
{
    public $di;

    public function run(): void
    {
        $this->di  = new CliDI();
        $dispatcher = new Dispatcher();

        $dispatcher->setDefaultNamespace('EngineStarterService\Task');
        $this->di->setShared('dispatcher', $dispatcher);

        $conf = ProcessEngineConfiguration::createProcessEngineConfigurationFromResource(realpath('./engine.cfg.xml'));
        $this->di->set("process_engine", $conf->buildProcessEngine());
                 
        $console = new Console($this->di);
        $argv = $_SERVER['argv'];
        $arguments = [];
        foreach ($argv as $k => $arg) {
            if ($k === 1) {
                $arguments['task'] = $arg;
            } elseif ($k === 2) {
                $arguments['action'] = $arg;
            } elseif ($k >= 3) {
                $arguments['params'][] = $arg;
            }
        }

        try {
            $console->handle($arguments);
        } catch (PhalconException $e) {
            fwrite(STDERR, $e->getMessage() . PHP_EOL);
            exit(1);
        } catch (\Throwable $throwable) {
            fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
            exit(1);
        } catch (\Exception $exception) {
            fwrite(STDERR, $exception->getMessage() . PHP_EOL);
            exit(1);
        }
    }
}
