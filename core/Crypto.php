<?php
if (!function_exists('generateKeys')) {
    function generateKeys() {
        $keyPair = sodium_crypto_sign_keypair();
        $publicKey = sodium_crypto_sign_publickey($keyPair);
        $secretKey = sodium_crypto_sign_secretkey($keyPair);
        return [
            'publicKey' => $publicKey,
            'secretKey' => $secretKey
        ];
    }
}

// Удалите функции encryptMessage, decryptMessage, signMessage и verifyMessage
?>
