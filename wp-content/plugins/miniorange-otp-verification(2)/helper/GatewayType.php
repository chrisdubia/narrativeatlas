<?php

namespace OTP\Helper;

if(! defined( 'ABSPATH' )) exit;

use OTP\Objects\IGatewayType;
use OTP\Traits\Instance;

class GatewayType implements IGatewayType
{
    use Instance;

    /** @var GatewayType $gatewayType to invoke */
    private $gatewayType;

    public function __construct()
    {
        $gatewayTypeClass = get_mo_option("custome_gateway_type"); 
        $gatewayTypeClass = "OTP\Helper\Gateway\\" . ($gatewayTypeClass ? $gatewayTypeClass : "MoGatewayURL") ;
        $this->gatewayType = $gatewayTypeClass::instance();
    }

    /**
     * Handles the gateway response
     * 
     * @param mixed $response -  gateway response
     * @return status
     */
    public function handleGatewayResponse($response,$message,$phone)
    {   
    
        return $this->gatewayType->handleGatewayResponse($response,$message,$phone);
    }

    /**
     * invokes the respective method to send OTP
     * 
     * @param String $message  - message
     * @param String $phone    - phone number
     * @return response
     */
    public function sendOTPRequest($message,$phone)
    {
        return $this->gatewayType->sendOTPRequest($message,$phone);
    }

    /**
     * Retrives respective gateway configuration html
     * 
     * @param String $disabled  - conatains disabled or empty
     * @return String HTML view 
     */
    public function getGatewayConfigView($disabled,$gateway_url)
    {
        return $this->gatewayType->getGatewayConfigView($disabled,$gateway_url);
    }

    /**
     * Saves the settings for the respective gateway
     * 
     * @param Mixed $posted - Posted data
     */
    public function saveGatewayDetails($posted)
    {
        $this->gatewayType->saveGatewayDetails($posted);
    }

}