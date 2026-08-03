<?php
namespace App\Libraries;
class Aes {

    private $key;

    public function __construct() {
        return $this;
    }
    public function set_key($key){
        $this->key = $key;
        return $this;
    }
    public function encrypt($plaintext) {
        // AES-256 encryption
        $cipher = "aes-256-cbc";

        // Generate initialization vector
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        // Encrypt plaintext with AES-256-CBC cipher using the key and initialization vector
        $ciphertext = openssl_encrypt($plaintext, $cipher, $this->key, OPENSSL_RAW_DATA, $iv);
        // Combine the initialization vector and ciphertext
        $enc = base64_encode($iv . $ciphertext);
        return $enc;
    }

    public function decrypt($ciphertext) {
        // AES-256 decryption
        $cipher = "aes-256-cbc";
        // Decode the base64-encoded input string
        $ciphertext = base64_decode($ciphertext);
        // Extract the initialization vector from the input string
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($ciphertext, 0, $ivlen);

        // Extract the ciphertext from the input string
        $ciphertext = substr($ciphertext, $ivlen);
        // Decrypt ciphertext with AES-256-CBC cipher using the key and initialization vector
        $plaintext = openssl_decrypt($ciphertext, $cipher, $this->key, OPENSSL_RAW_DATA, $iv);
        return $plaintext;
    }
}
