<?php

class Paytree 
{
    const ENDPOINT = "https://api1.interview-assignments.paytree-network.nl";
    
    public $classloaded = false;
    private $key; //  API Key

    public function __construct()
    {
        if (extension_loaded('curl')) 
        {
            $this->classloaded = true;
        }
    }

    public function setKey($username, $password) 
    {
        $this->key = base64_encode("$username:$password");
    }

    public function getKey(): String 
    {
        return $this->key;
    }

    public function getPaymentMethods($key)
    {
        if ($key == "") { return; }

        $url = self::ENDPOINT . "/transaction/methods";
        
        $header = [
            "Content-type: application/json; charset=utf-8",
            "Authorization: " . "Basic $key"
        ];

        $cl = curl_init();
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cl, CURLOPT_HTTPHEADER, $header);
        $response = json_decode( curl_exec($cl) );
        curl_close($cl);

        if (empty($response)) { return; }

        return $response->methods;
    }

    public function pay($key, $amount, $paymentMethodID) 
    {
        if ($key == "" || $amount == 0 || $paymentMethodID == "") { return; }

        $url = self::ENDPOINT . "/transaction/start";
        
        $header = [
            "Content-type: application/json; charset=utf-8",
            "Authorization: " . "Basic $key"
        ];

        $cl = curl_init();
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_POST, 1);
        curl_setopt($cl, CURLOPT_POSTFIELDS, json_encode(["amount" => $amount, "paymentMethodId" => $paymentMethodID]));
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cl, CURLOPT_HTTPHEADER, $header);
        $response = json_decode(curl_exec($cl));
        curl_close($cl);

        if (empty($response)) { return; }

        return $response->transactionId;
    } 

    public function getPaymentStatus($key, $transactionID) 
    {
        if ($key == "" || $transactionID == "") { return; }

        $url = self::ENDPOINT . "/transaction/status/$transactionID";
        
        $header = [
            "Content-type: application/json; charset=utf-8",
            "Authorization: " . "Basic $key"
        ];

        $cl = curl_init();
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cl, CURLOPT_HTTPHEADER, $header);
        $response = json_decode(curl_exec($cl));
        curl_close($cl);

        if (empty($response)) { return; }

        return $response->status;
    }
}

?>