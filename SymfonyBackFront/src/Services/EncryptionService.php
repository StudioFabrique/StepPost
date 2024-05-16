<?php 

namespace App\Services;

class EncryptionService
{
    private $EncodeurPublicKey;
    private $DecodeurPublicKey;
    private $EncodeurSecretKey;
    private $DecodeurSecretKey;
    private $EncodeurKeypair;
    private $DecodeurKeypair;
    private $Nonce;
    private $Key;

    public function __construct(string $EncodeurPublicKey, string $DecodeurPublicKey, string $EncodeurSecretKey, string $DecodeurSecretKey, string $Nonce, string $Key)
    {
        $this->EncodeurPublicKey = base64_decode($EncodeurPublicKey);
        $this->DecodeurPublicKey = base64_decode($DecodeurPublicKey);

        $this->EncodeurSecretKey = base64_decode($EncodeurSecretKey);
        $this->DecodeurSecretKey = base64_decode($DecodeurSecretKey);

        $this->EncodeurKeypair = sodium_crypto_box_keypair_from_secretkey_and_publickey( $this->EncodeurSecretKey, $this->DecodeurPublicKey);
        $this->DecodeurKeypair = sodium_crypto_box_keypair_from_secretkey_and_publickey( $this->DecodeurSecretKey, $this->EncodeurPublicKey);

        $this->Nonce = base64_decode($Nonce);
        $this->Key = base64_decode($Key);
    }

    public function encryptBlob(string $data): string 
    {
        // Chiffrer les données avec la clé publique de Bob
        // try {
        //     $encryptedData = sodium_crypto_box($data, $this->Nonce, $this->EncodeurKeypair);
        //     return base64_encode($encryptedData); // Encode en base64 pour le stockage
        // } catch (\Exception $e) {
        //     throw new \Exception('Erreur de chiffrement : ' . $e->getMessage());
        // }
        $encrypted = sodium_crypto_secretbox($data, $this->Nonce, $this->Key);
        return $encrypted;
    }

    public function decryptBlob(string $encryptedData): string
    {
        // try {
        //     $decodedData = base64_decode($encryptedData); // Décoder depuis base64
        //     $decryptedData = sodium_crypto_box_open($decodedData, $this->Nonce, $this->DecodeurKeypair);

        //     if ($decryptedData === false) {
        //         throw new \Exception('Déchiffrement échoué');
        //     }

        //     return $decryptedData;
        // } 
        // catch (\Exception $e) {
        //     throw new \Exception('Erreur de déchiffrement : ' . $e->getMessage());
        // }

        $decrypted = sodium_crypto_secretbox_open($encryptedData, $this->Nonce, $this->Key);
        return $decrypted;
    }

}