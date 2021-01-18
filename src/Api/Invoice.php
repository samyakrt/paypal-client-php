<?php
namespace Samyakrt\PaypalClientPhp\Api;

use Illuminate\Support\Facades\Http;
use Samyakrt\PaypalClientPhp\Auth\PaypalAuth;
use Samyakrt\PaypalClientPhp\Exceptions\FailedException;
use Samyakrt\PaypalClientPhp\Exceptions\InvalidConfiguration;
use Samyakrt\PaypalClientPhp\Exceptions\UnauthenticatedException;

class Invoice {

    /**
     * access token for api authorization
     * @var $accessToken
     */
    private $accessToken;
    private $baseUrl;

    public function __construct(string $accessToken) {
        $this->accessToken = $accessToken;
        $this->baseUrl = config('paypal.mode') == 'sandbox' ? 'https://api.sandbox.paypal.com' : 'https://api.paypal.com';
        $this->baseUrl .= '/v2/invoicing/invoices' ;
    }

    /**
     * get all the invoices
     */
    public function index(array $queryParams) {

        $url = $this->baseUrl;
        $url .= !empty($queryParams) ? '?'.http_build_query($queryParams) : '';
        $response = Http::withHeaders([
                                        'Authorization' => 'Bearer '.$this->accessToken,
                                        'Content-Type' => 'application/json' 
                                    ])
                                    ->get($this->baseUrl);

    if($response->successful()) {
        return $response->json();
    }

    \Log::error('failed to list invoices',['_trace' =>$response->json() ]);

    if($response->status() == 401) {
        throw UnauthenticatedException::forInvoiceList();
    }

    throw FailedException::forInvoiceList();
    }

    /**
     * creates an draft paypal invoice.
     * https://developer.paypal.com/docs/api/invoicing/v2/#invoices_create
     * @param input : array
     */
    public function create(array $input) : array {

        if(empty($input['invoicer'])) {
                    //get the user info
            $userInfo = (new PaypalAuth)->userInfo($this->accessToken);

            $userName = explode(' ',$userInfo['name']);
            $invoicer = [
                'email_address' => $userInfo['emails'][0]['value'],
                'name' => [
                    'given_name' => $userName[0],
                    'surname' => $userName[1]
                ]
            ];

            $input['invoicer'] = $invoicer;
        }

        if(!array_key_exists('detail',$input)) {
            throw InvalidConfiguration::forInvoiceCreate();
        }

        if(!array_key_exists('primary_recipients',$input)) {
            throw InvalidConfiguration::forInvoiceCreate();
        }

        $response = Http::withHeaders([
                                        'Authorization' => 'Bearer '.$this->accessToken,
                                        'Content-Type' => 'application/json' 
                                    ])
                                    ->post($this->baseUrl,$input);


        if($response->successful()) {
            \Log::info('Invoice created successfully.');
            $method = strtolower($response->json()['method']);

            return $this->details('',$response->json()['href']);
           
        }

        if($response->status() == 401) {
            \Log::debug('Token expired for user_id:',auth()->id() ??'',['trace' => $response->json()]);
            throw UnauthenticatedException::forInvoiceCreate();
        }

        \Log::debug('Invoice create failed.',['trace' => $response->json()]);
        throw FailedException::forInvoiceCreate();
    }

    public function details($invoice_id,$url='')  {

        if(empty($url)) {
            $url = $this->baseUrl.'/'.$invoice_id;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer ".$this->accessToken,
            'Content-Type' => 'application/json'
        ])->get($url);

        if($response->successful()) {
            return $response->json();
        }

        throw FailedException::forInvoiceDetail();
    }

    /**
     * sends paypal invoice 
     *  https://developer.paypal.com/docs/api/invoicing/v2/#invoices_send
     * @param invoice_id
     * @return boolean
     */

    public function send(string $invoice_id,array $input) : boolean {

        if(empty($invoice_id)) {
            throw FailedException::forInvoiceSend('Could not send invoice,invoice id is missing. Try again.');
        }

        $url = $this->baseUrl.'/'.$invoice_id.'/send';

        $response = Http::withHeaders([
                                        'Authorization' => 'Bearer '.$this->accessToken,
                                        'Content-Type' => 'application/json'
                                    ])
                                    ->post($url,$input);

        if($response->successful()) {
            return true;
        }

        if($response->status() == 401) {
            throw UnauthenticatedException::forInvoiceSend();
        }

        if($response->status() == 422) {
            throw FailedException::forInvoiceSend('Invoice already sent.',422);
        }

        throw FailedException::forInvoiceSend();
    }

    /**
     * updates the paypal invoice
     * https://developer.paypal.com/docs/api/invoicing/v2/#invoices_update
     * @var string $invoice_id invoice id from paypal
     * @var array $queryParams ['send_to_recipient' => bool, 'send_to_invoicer+'=> bool]
     * @var array input
     * 
     */

    public function update(string $invoice_id,array $queryParams, array $input) {

        if(empty($invoice_id)) {
            throw FailedException::forInvoiceUpdate('Could not send invoice,invoice id is missing. Try again.');
        }

        $url = $this->baseUrl.'/'.$invoice_id;
        $url .= !empty($queryParams) ? '?'.http_build_query($queryParams) : '';
        $response = Http::withHeaders([
                                    'Authorization' => "Bearer ".$this->accessToken,
                                    'Content-Type' => 'application/json'
                                    ])
                                    ->put($url,$input);
        
        if($response->successful()) {
            return $this->details('',$response->json()['href']);
        }

        if($response->status() == 401) {
            throw UnauthenticatedException::forInvoiceUpdate();
        }

        throw FailedException::forInvoiceUpdate($response->json()['details'][0]['description']);
    }
    /**
     * deletes invoice
     * https://developer.paypal.com/docs/api/invoicing/v2/#invoices_delete
     * @param invoice_id
     */

    public function delete(string $invoice_id) {

        if(empty($invoice_id)) {
            throw FailedException::forInvoiceSend('Could not delete invoice,invoice id is missing. Try again.');
        }
        
        $url = $this->baseUrl.'/'.$invoice_id;
        $response = Http::withHeaders([
                                        'Content-Type' => "application/json",
                                        'Authorization' => "Bearer ".$this->accessToken
                                    ])
                                    ->delete($url);

        if($response->successful()) {
            return true;
        }

        if($response->status() == 401) {
            throw UnauthenticatedException::forInvoiceDelete();
        }

        throw FailedException::forInvoiceDelete($response->json()['details'][0]['description']);
    }

    public function sendReminder($invoice_id,array $input) {

        $url = $this->baseUrl;
        $url .= '/'.$invoice_id.'/remind';
        
        if(empty($invoice_id)) {
            throw FailedException::forInvoiceSend('Could not send invoice,invoice id is missing. Try again.');
        }

        $response = Http::withHeaders([
                                        'Authorization' => "Bearer ".$this->accessToken,
                                        'Content-Type' => 'application/json'
                                        ])
                                        ->post($url,$input);

        if($response->successful()) {
            return true;
        }

        if($response->status() == 401) {
            throw UnauthenticatedException::forInvoiceSend();
        }

        throw FailedException::forInvoiceSend($response->json()['details'][0]['description']);
    
    }
    
}