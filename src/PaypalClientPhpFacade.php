<?php

namespace Samyakrt\PaypalClientPhp;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Samyakrt\PaypalClientPhp\Skeleton\SkeletonClass
 */
class PaypalClientPhpFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paypal-client-php';
    }
}
