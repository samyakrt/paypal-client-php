<?php

namespace Samyakrt\PaypalClientPhp\Auth;

use Illuminate\Support\Facades\Http;
use Samyakrt\PaypalClientPhp\Exceptions\FailedException;
use Samyakrt\PaypalClientPhp\Exceptions\InvalidConfiguration;
use Samyakrt\PaypalClientPhp\Exceptions\UnauthenticatedException;

class PaypalAuth {


     public $redirectUrl = '';
     protected $clientId = '';
     protected $clientSecret = '';
     
     /**
      * contains scopes for paypal
      * @var string 
      */
     public $scopes = '';

     /**
      * url to which it redirects to after user grants app access.
      * @var string 
      */
     protected $authRedirectUrl = '';

     protected $baseUrl = '';
     protected $mode = '';

    public function __construct($settings = [] ) {

        if(empty($settings)) {
            $settings= config('paypal');
        }

        if(!isset($settings)) {
            throw InvalidConfiguration::ConfigNotSpecified();
        }

        if(empty($settings['client_id'])) {
            throw InvalidConfiguration::ClientIdNotSpecified();
        }
        else {
            $this->clientId = $settings['client_id'];
        }

        if(empty($settings['client_secret'])) {
            throw InvalidConfiguration::ClientSecretNotSpecified();
        }
        else {
            $this->clientSecret = $settings['client_secret'];
        }

        if(isset($settings['scopes'])) {
            $this->scopes = 'openid profile email address https://uri.paypal.com/services/invoicing';
        }
        
        if($settings['mode'] == 'sandbox') {
            $this->redirectUrl = 'https://www.sandbox.paypal.com/connect';
            $this->baseUrl = 'https://api.sandbox.paypal.com';
        }
        else {
            $this->redirectUrl = 'https://www.paypal.com/connect';
            $this->baseUrl = 'https://api.paypal.com';
        }
        $this->mode = $settings['mode'];
        
        $this->authRedirectUrl = $settings['auth_redirect_uri'];
    }

    /**
     * url to paypal login to grant scopes access from the client
     */
    public function getAuthorizationURL() {
        $auth_scopes = str_replace(' ','+',$this->scopes);

        return $this->redirectUrl.'?flowEntry=static'
                                 .'&scope='.$auth_scopes
                                 .'&redirect_url='.$this->authRedirectUrl
                                 .'&client_id='.$this->clientId
                                 .'&response_type=code';
    }

    /**
     * @param code
     * @return json
     */
    public function generateAccessToken(string $code) {
        $headerToken = $this->clientId.':'.$this->clientSecret;
        $curl = curl_init();
        $data = array(
                        CURLOPT_URL => $this->baseUrl."/v1/oauth2/token",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_POSTFIELDS => http_build_query(array(
                            'grant_type'=>'authorization_code',
                            'code'=> $code,
                        )),
                        CURLOPT_HTTPHEADER => array(
                            "Content-Type: application/x-www-form-urlencoded",
                        ),
                        CURLOPT_USERPWD => $headerToken
                    );
    
        curl_setopt_array($curl, $data);
    
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    
        if($status_code>=400){
            // throw new \Exception($response,400);
            throw FailedException::forInvalidAuthorizationCode();
        }

        return json_decode($response,true);
    }

    /**
     * re-generates access token after its expiration
     * @param refreshToken
     * @return json
     */
    public function refreshToken(string $refreshToken) {

        $headerToken = $this->clientId.':'.$this->clientSecret;
        $curl = curl_init();
        $data = array(
                        CURLOPT_URL => $this->baseUrl."/v1/oauth2/token",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_POSTFIELDS => http_build_query(array(
                            'grant_type'=>'refresh_token',
                            'refresh_token'=> $refreshToken,
                        )),
                        CURLOPT_HTTPHEADER => array(
                            "Content-Type: application/x-www-form-urlencoded",
                        ),
                        CURLOPT_USERPWD => $headerToken
                    );
    
        curl_setopt_array($curl, $data);
    
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    
        if($status_code>=400){
            throw FailedException::forInvalidRefreshToken($response);
        }

        return json_decode($response,true);
    }

    /**
     * get client profile information
     * @param accessToken
     */
    public function userInfo(string $accessToken) {
        $url = $this->baseUrl.'/v1/identity/oauth2/userinfo?schema=paypalv1.1';
        
        $response = Http::withHeaders([
                                        'Authorization' => "Bearer ".$accessToken,
                                        'Content-Type' => 'application/json' 
                                     ])
                                     ->get($url);

        if($response->ok()) {
            return $response->json();
        }

        if($response->status() == 401) {
            throw UnauthenticatedException::forUserInfo();
        }
        throw FailedException::forUserInfo();
    }
}