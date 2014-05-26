<?php namespace Vataware\VatsimAuth;

class SSOSignatureMethodRSASHA1 extends OAuth\SignatureMethodRSASHA1 {
    
    private $cert = false;
    
    public function __construct($cert){
        $this->cert = $cert;
    }
    
    public function fetch_private_cert(&$request) {
        return $this->cert;
    }
    
}