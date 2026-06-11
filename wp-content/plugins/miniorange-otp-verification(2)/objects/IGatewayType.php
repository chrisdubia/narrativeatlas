<?php

namespace OTP\Objects;

interface IGatewayType
{
    public function handleGatewayResponse($response,$message,$phone);
    public function sendOTPRequest($message,$phone);
    public function getGatewayConfigView($disabled, $gateway_url);
    public function saveGatewayDetails($posted);


}