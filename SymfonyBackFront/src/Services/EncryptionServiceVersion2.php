<?php

namespace App\Services;

class EncryptionServiceVersion2
{
    private $secretKeys;
    private $secretIvs;

    #Récupération des valeurs des clés stockées dans le .env + yaml
    function __construct(string $secretIvs, string $secretKeys)
    {
        $this->secretIvs = $secretIvs;
        $this->secretKeys = $secretKeys;

    }
    #Fonction pour encrypter et decrypter, 2 choix possibles
    function encrypt_decrypt($action, $data){
        $output = false;
        #algo de chiffrement
        $cipher_algo = "AES-256-CBC";

        #clé secrète + Hachage SHA-256: Génère une sortie de 256 bits
        $secretKey = hash('sha256', $this->secretKeys);

        #secretOpenssl + substr garantit que l'IV a la longueur nécessaire de 128 bits.
        $secretIv = substr(hash('sha256', $this->secretIvs), 0, 16);

        #cryptage
        if($action == "encrypt"){
            #message, algo, clé secrete, option (ici 0 car pas d'options), secretIv(vecteur d'initialisation)
            $encrypted = openssl_encrypt($data, $cipher_algo, $secretKey, 0, $secretIv);
            $output = base64_encode($encrypted);
        }
        #decryptage
        elseif($action == "decrypt"){
            #De même qu'encrypt mais on decode le message cette fois
            $output = openssl_decrypt(base64_decode($data), $cipher_algo, $secretKey, 0, $secretIv);
        }
        return $output;
    }
}