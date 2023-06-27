[![Latest Stable Version](https://poser.pugx.org/bingo-soft/jabe/v/stable.png)](https://packagist.org/packages/bingo-soft/jabe)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bingo-soft/jabe/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/bingo-soft/jabe/?branch=main)


# Jabe - Just Another BPMN Engine
  ![horiz logo](https://github.com/johnsantosDev/jabe/assets/92297941/4139fe15-ef33-4022-a310-ea23d2b45f7f)



Jabe (/dʒæbi/) - is a powerful and flexible BPMN workflow engine. It is designed to streamline the creation and management of BPMN workflows by providing developers with an array of functionality to build, deploy, and manage workflows with ease.

Unlike other popular BPMN engines, such as Activity, Camunda and Flowable, Jabe allows to create Service Tasks using PHP language. In future releases Jabe is planning to implement a domain-specific language for writing Script Tasks in PHP.

At current stage of development Jabe does not have REST API, but its core API is sufficient to integrate and enhance your applications.

# Installation

Install Jabe, using Composer:

```
composer require bingo-soft/jabe
```

# Running tests

```
./vendor/bin/phpunit ./tests
```

## Acknowledgements

Jabe draws inspiration from [camunda](https://github.com/camunda/camunda-bpm-platform) and [flowable](https://github.com/flowable/flowable-engine) libraries.

## License

MIT
