<?php

namespace App\Services;

class EncryptionServiceVersion2
{
    function encrypt_decrypt($action, $data){
        $output = false;
        $cipher_algo = "AES-256-CBC";

        $secretKeys = "w8ZyjG7r+v5F58KwEj+/nT0uFz0zH52fF3ZIm3kEyTc=";
        $secretKey = hash('sha256', $secretKeys);

        $secretIvs = "GcAd7JH2K8LYDhEpZPpN0g==";
        $secretIv = substr(hash('sha256', $secretIvs), 0, 16);

        if($action == "encrypt"){
            $encrypted = openssl_encrypt($data, $cipher_algo, $secretKey, 0, $secretIv);
            $output = base64_encode($encrypted);
        }elseif($action == "decrypt"){
            $output = openssl_decrypt(base64_decode($data), $cipher_algo, $secretKey, 0, $secretIv);
        }
        return $output;
    }
}