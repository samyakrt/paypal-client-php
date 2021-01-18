<?php

namespace Samyakrt\PaypalClientPhp\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthenticatedException extends HttpException
{

    public static function forUserInfo($message = null, $status_code = 401)
    {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t get  user information. Try again',null, [], $status_code);
    }

    public static function forInvoiceCreate($message = null, $status_code = 401)
    {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t create invoice. Try again',null, [], $status_code);
    }

    public static function forInvoiceSend($message = null, $status_code = 401)
    {
        return new static(401, $message ?? 'Something went wrong, Couldn\'t send invoice. Try again',null, [], $status_code);
    }

    public static function forInvoiceUpdate($message = null, $status_code = 401)
    {
        return new static(401, $message ?? 'Something went wrong, Couldn\'t update invoice. Try again',null, [], $status_code);
    }

    public static function forInvoiceList($message = null, $status_code = 401)
    {
        return new static(401, $message ?? 'Something went wrong, Couldn\'t get invoices. Try again',null, [], $status_code);
    }

    public static function forInvoiceDelete($message = null, $status_code = 401)
    {
        return new static(401, $message ?? 'Something went wrong, Couldn\'t delete invoice. Try again',null, [], $status_code);
    }

    public static function forInvoiceDownload($message = null, $status_code = 401)
    {
        return new static(401, $message ?? 'Something went wrong, Couldn\'t download invoice. Try again',null, [], $status_code);
    }   
}
