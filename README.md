[![Latest Stable Version](https://poser.pugx.org/bingo-soft/bpmplatform/v/stable.png)](https://packagist.org/packages/bingo-soft/bpmplatform)
[![Build Status](https://app.travis-ci.com/bingo-soft/bpmplatform.svg?branch=main)](https://app.travis-ci.com/bingo-soft/bpmplatform.svg?branch=main)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bingo-soft/bpmplatform/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/bingo-soft/bpmplatform/?branch=main)


# About

BpmPlatform is a PHP framework for workflow and process automation. Its core consists of XML model, on top of which you can create custom models with domain-specific elements and relationships. It also implements BPMN model fully compatible with BPMN 2.0 specification.

# Installation

Install BpmPlatform, using Composer:

```
composer require bingo-soft/bpmplatform
```

# Running tests

```
./vendor/bin/phpunit ./tests
```

# Example 1

```php
//create new invoice business process

Bpmn::getInstance()->createProcess()
        ->executable()
        ->startEvent()
          ->name("Invoice received")
          ->formKey("embedded:app:forms/start-form.html")
        ->userTask()
          ->name("Assign Approver")
          ->formKey("embedded:app:forms/assign-approver.html")
          ->assignee("demo")
        ->userTask("approveInvoice")
          ->name("Approve Invoice")
          ->formKey("embedded:app:forms/approve-invoice.html")
          ->assignee('${approver}')
        ->exclusiveGateway()
          ->name("Invoice approved?")
          ->gatewayDirection("Diverging")
        ->condition("yes", '${approved}')
        ->userTask()
          ->name("Prepare Bank Transfer")
          ->formKey("embedded:app:forms/prepare-bank-transfer.html")
          ->candidateGroups("accounting")
        ->serviceTask()
          ->name("Archive Invoice")
          ->setClass("org.test.bpm.example.invoice.service.ArchiveInvoiceService")
        ->endEvent()
          ->name("Invoice processed")
        ->moveToLastGateway()
        ->condition("no", '${!approved}')
        ->userTask()
          ->name("Review Invoice")
          ->formKey("embedded:app:forms/review-invoice.html" )
          ->assignee("demo")
         ->exclusiveGateway()
          ->name("Review successful?")
          ->gatewayDirection("Diverging")
        ->condition("no", '${!clarified}')
        ->endEvent()
          ->name("Invoice not processed")
        ->moveToLastGateway()
        ->condition("yes", '${clarified}')
        ->connectTo("approveInvoice")
        ->done();
```

# Example 2

```php
// Read business process from file

$fd = fopen('test.bpmn', 'r+');
$modelInstance = Bpmn::getInstance()->readModelFromStream($fd);
```

## Acknowledgements

BpmPlatform draws inspiration from [camunda](https://github.com/camunda/camunda-bpm-platform) library.

## License

MIT