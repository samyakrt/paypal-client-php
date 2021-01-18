<?php

namespace Samyakrt\PaypalClientPhp\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FailedException extends HttpException
{
    public static function forInvalidAuthorizationCode($message = null , $status_code = 400) {
        return new static($status_code, $message ?? 'Something is wrong with code, Couldn\'t generate access token.');
    }

    public static function forUserInfo($message = null, $status_code = 401)
    {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t get  user information. Try again',null, [], $status_code);
    }

    public static function forInvalidRefreshToken($message = null , $status_code = 400) {
        return new static($status_code, $message ?? 'Something went wrong with refresh token, Couldn\'t generate access token.');
    }

    public static function forInvoiceCreate($message = null, $status_code = 400)
    {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t create invoice.');
    }

    public static function forInvoiceUpdate($message = null, $status_code = 400)
    {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t update invoice.');
    }

    public static function forInvoiceSend($message = null, $status_code = 400)
    {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t send invoice.');
    }

    public static function forInvoiceDelete($message = null, $status_code = 400)
    {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t delete invoice.');
    }

    public static function forInvoiceDownload($message = null, $status_code = 400) {
        return new static($status_code, $message ?? 'Something went wrong, Couldn\'t download invoice.');
    }

    public static function forInvoiceList($message = null, $status_code = 400)
    {
        return new static(401, $message ?? 'Something went wrong, Couldn\'t get invoices. Try again',null, [], $status_code);
    }

    public static function forInvoiceDetail($message = null, $status_code = 400) {
        return new static(401, $message ?? 'Something went wrong, Couldn\'t get invoice detail. Try again',null, [], $status_code);
    }

}
