<?php

namespace Samyakrt\PaypalClientPhp\Tests;

use Orchestra\Testbench\TestCase;
use Samyakrt\PaypalClientPhp\PaypalClientPhpServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PaypalClientPhpServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
