<?php

namespace Samyakrt\PaypalClientPhp\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidConfiguration extends HttpException
{
    public static function ConfigNotSpecified($message = null,$status_code = 400) {
        return new static($status_code,$message ?? 'Please provide required configurations. Make sure it is set in environment file.',null,[],$status_code);
    }
    
    public static function ClientIdNotSpecified($message = null, $status_code = 400)
    {
        return new static($status_code, $message ?? 'You must provide client ID. Make sure it is set in environment file.',null, [], $status_code);
    }

    public static function ClientSecretNotSpecified($message = null, $status_code = 400)
    {
        return new static($status_code, $message ?? 'You must provide Client Secret. Make sure it is set in environment file.',null, [], $status_code);
    }

    public static function forInvoiceCreate($message = null, $status_code = 400) {
        return new static($status_code, $message ?? 'You must provide all required fields to create invoice. Check paypal api docs for further details.',null, [], $status_code);
    }
}
