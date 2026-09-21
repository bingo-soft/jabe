<?php

namespace Tests\Impl\JobExecutor;

use Jabe\Impl\JobExecutor\JobAcquisitionContext;
use PHPUnit\Framework\TestCase;

class JobAcquisitionContextTest extends TestCase
{
    public function testStoresPhpErrorAsAcquisitionException(): void
    {
        $context = new JobAcquisitionContext();
        $error = new \Error('worker acquisition failure');

        $context->setAcquisitionException($error);

        $this->assertSame($error, $context->getAcquisitionException());
    }
}
