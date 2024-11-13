<?php
namespace Utils;

function encrypt($data) {
    $key = getenv("EDOC_ENCRYPTION_KEY");
    $cipher = "aes-256-gcm";
    $ivlen = openssl_cipher_iv_length($cipher);

    // Generate random IV that is unique for each encryption
    $iv = openssl_random_pseudo_bytes($ivlen);

    // Encrypt data and get the authentication tag
    $encrypted_data = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

    // Check if data was successfully encrypted
    if ($encrypted_data === false) {
        throw new \Exception("Data encryption failed.");
    }

    // Return base64-encoded IV, tag, and encrypted data
    return base64_encode($iv . $tag . $encrypted_data);
}

function decrypt($data) {
    $key = getenv("EDOC_ENCRYPTION_KEY");
    $cipher = "aes-256-gcm";
    $ivlen = openssl_cipher_iv_length($cipher);

    // Decode base64 and extract IV, tag, and encrypted data
    $data = base64_decode($data);
    $iv = substr($data, 0, $ivlen);
    $tag = substr($data, $ivlen, 16);   // AES-GCM tags are 16 bytes
    $encrypted_data = substr($data, $ivlen + 16);

    // Decrypt data
    $decrypted_data = openssl_decrypt($encrypted_data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

    // Check if data was successfully decrypted
    if ($decrypted_data === false) {
        throw new \Exception("Data decryption failed.");
    }

    // Return decrypted data
    return $decrypted_data;
}