<?php

$key = sodium_crypto_secretbox_keygen();


$aliceKeypair = sodium_crypto_box_keypair();
$alicePublicKey = sodium_crypto_box_publickey($aliceKeypair);
$aliceSecretKey = sodium_crypto_box_secretkey($aliceKeypair);


$bobKeypair = sodium_crypto_box_keypair();
$bobPublicKey = sodium_crypto_box_publickey($bobKeypair);
$bobSecretKey = sodium_crypto_box_secretkey($bobKeypair);


$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);


$encodedAlicePublicKey = base64_encode($alicePublicKey);
$encodedAliceSecretKey = base64_encode($aliceSecretKey);
$encodedBobPublicKey = base64_encode($bobPublicKey);
$encodedBobSecretKey = base64_encode($bobSecretKey);
$encodedNonce = base64_encode($nonce);
$encodedKey = base64_encode($key);


echo "Alice Public Key: $encodedAlicePublicKey\n";
echo "Alice Secret Key: $encodedAliceSecretKey\n";
echo "Bob Public Key: $encodedBobPublicKey\n";
echo "Bob Secret Key: $encodedBobSecretKey\n";


file_put_contents('SecretKey.txt', $encodedKey);
file_put_contents('nonce.txt', $encodedNonce);
file_put_contents('alice_public_key.txt', $encodedAlicePublicKey);
file_put_contents('alice_secret_key.txt', $encodedAliceSecretKey);
file_put_contents('bob_public_key.txt', $encodedBobPublicKey);
file_put_contents('bob_secret_key.txt', $encodedBobSecretKey);

echo "Keys have been saved to files.\n";