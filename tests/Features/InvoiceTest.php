<?php


namespace Samyakrt\PaypalClientPhp\Tests\Features;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Http;
use Samyakrt\PaypalClientPhp\Api\Invoice;
use Samyakrt\PaypalClientPhp\Auth\PaypalAuth;
use Samyakrt\PaypalClientPhp\PaypalClientPhpServiceProvider;


class InvoiceTest extends TestCase {

    protected $invoice;
    protected $invoice_id = '';

    protected function getPackageProviders($app)
    {
        return [PaypalClientPhpServiceProvider::class];
    }

    public function setUp() : void{
        parent::setUp();
        $this->invoice = new Invoice('A23AAIExMRuj4qUKSTBC7hI7iLBw3g3RJ9pTGaGL0FN2swC8zqUZMWXb2KJaXRTvvk2bd-yWlJJctFka0WPvb3arkMfHZ6nvg');
    }

    /**
     
     */
    public function it_creates_a_draft_invoice() {

        $response = (array) $this->invoice->create([
                                'detail' => [
                                    'currency_code' => 'USD'
                                ],
                                'primary_recipients' => [
                                    [
                                        'billing_info' => [
                                            'email_address' => 'bretty.test@gmail.com',
                                            'business_name' => 'Company Name',
                                            'phones' => [
                                                [
                                                    'country_code' => '001',
                                                    'national_number' => '9860930254',
                                                ]
                                            ]
                                    ]]
                                ],
                                'items' => [
                                    [
                                        'name' => 'Test',
                                        'unit_of_measure' => 'AMOUNT',
                                        'unit_amount' => [
                                            'currency_code' => "USD",
                                            'value' => '50'
                                        ],
                                        'quantity' => 1
                                    ]
                                ]
                            ]);
        
        $this->invoice_id = $response['id'];
        $this->assertArrayHasKey('id',$response);
    }

    /**
     
     */

    public function it_sends_an_invoice() {
        try{
            $response = $this->invoice->send('INV2-3WEU-W85A-4N2V-ZH6B',[
                'send_to_recipient' => true,
                'subject' => "Hey man you need to pay me fast.",
                'note' => "it's urgent bro."
            ]);
            $this->assertTrue($response);
        }
        catch(\Exception $e) {
            $this->assertEquals($e->getMessage(),'Invoice already sent.');
        }
    }

    /**
     
     */

    public function it_resends_an_invoice() {
        try{
            $response = $this->invoice->sendReminder('INV2-3WEU-W85A-4N2V-ZH6B',[
                                        'send_to_recipient' => true,
                                        'subject' => "Hey man you need to pay me fast.",
                                        'note' => "it's urgent bro."
                                    ]);
            $this->assertTrue($response);
        }
        catch(\Exception $e) {
            $this->assertEquals($e->getMessage(),'Invoice already sent.');
        }
    }
    /**
     
     */

    public function it_fails_to_delete_an_sent_invoice() {
        
        try {
            $response = $this->invoice->delete('INV2-3WEU-W85A-4N2V-ZH6B');
            $this->assertTrue($response);
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(),"Only invoices in draft, scheduled, or canceled status can be deleted.");
        }
    }

    /**
     
     */
    public function it_deletes_an_invoice() {
        $response = $this->invoice->delete('INV2-3VZG-HQ78-3U98-E5U8');
        $this->assertTrue($response);
    }

    /**
     
     */
    public function it_gets_invoice_details() {
        $response = $this->invoice->details('INV2-W8JC-WJFE-SNWG-JU9E');
        var_dump($response);
        $this->assertArrayHasKey('id',$response);
    }
    /**
     *@test
     */

    public function it_updates_an_invoice() {
        $response = $this->invoice->update('INV2-5ERD-K9GK-FYPW-S5LX',['send_to_recipient' => true],[
            'detail' => [
                'currency_code' => 'USD'
            ],
            'items' => [
                [
                    'id' => 'ITEM-1UW007134D191905N',
                    'name' => "new test",
                    'quantity' => '1',
                    'unit_amount' => [
                        'currency_code' => "USD",
                        'value' => '200'
                    ],
                ]
            ]

        ]);
        $this->assertArrayHasKey('id',$response);
    }


}