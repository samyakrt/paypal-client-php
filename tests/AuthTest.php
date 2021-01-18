<?php

namespace Samyakrt\PaypalClientPhp\Tests;

use Orchestra\Testbench\TestCase;
use Samyakrt\PaypalClientPhp\Auth\PaypalAuth;
use Samyakrt\PaypalClientPhp\PaypalClientPhpServiceProvider;

class AuthTest extends TestCase
{

    protected $paypal_auth_obj;

    protected function getPackageProviders($app)
    {
        return [PaypalClientPhpServiceProvider::class];
    }

    public function setUp(): void
    {
      parent::setUp();
      // additional setup
      $this->paypal_auth_obj = new PaypalAuth;
    }


    /** @test */
    public function it_gets_authorization_url() {
        $config = config('paypal');

        $auth_scopes = str_replace(' ','+',$config['scopes']);

        $supposed_url =  $this->paypal_auth_obj->redirectUrl.'?flowEntry=static'
                                 .'&scope='.$auth_scopes
                                 .'&redirect_url='.$config['auth_redirect_uri']
                                 .'&client_id='.$config['client_id']
                                 .'&response_type=code';
        
        $generated_url = $this->paypal_auth_obj->getAuthorizationURL();
        $this->assertEquals($supposed_url,$generated_url);
    }

    /** 
     * @test
     */
    public function it_throws_exception_when_generating_access_token() {
        try{
            $this->paypal_auth_obj->generateAccessToken('123');
        }
        catch(\Samyakrt\PaypalClientPhp\Exceptions\FailedException $e) {
            $message = $e->getMessage();
         $this->assertEquals($message,"Something is wrong with code, Couldn't generate access token.");
        }
    }

    /**
     * @test
    */
    public function it_generates_refresh_token() {
        $response = $this->paypal_auth_obj->refreshToken('R23AALWGmGwmaKBWAW-czjxD34-rSXYpXbTKZ2G1pB2I3A4UHrBmVprJCi2WqRAOzMmBLK8L8hsXibq02t-GkDVhcV83xqz8ye658B4LzyPBlOrHdk4cyTc0IuqTDOurcmbmH3NSThhyuhgS-lVGQ');
        var_dump($response);
        $this->assertArrayHasKey('access_token',$response);
    }


}
