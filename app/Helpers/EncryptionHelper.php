<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class EncryptionHelper {
    /**
     * Encrypt value using credential-specific key
     */
    public static function encrypt(string $value): string {
        $method = env('ENCRYPTION_METHOD', 'AES-256-CBC');
        $credentialKey = env('ENCRYPT_CREDENTIAL_KEY');

        if ($method === 'AES-256-CBC') {
            // Use credential-specific key if available, otherwise fallback to APP_KEY
            if ($credentialKey) {
                return self::encryptWithKey($value, $credentialKey);
            }
            return Crypt::encryptString($value);
        }

        return $value;
    }

    /**
     * Decrypt value using credential-specific key
     */
    public static function decrypt(string $value): string {
        $method = env('ENCRYPTION_METHOD', 'AES-256-CBC');
        $credentialKey = env('ENCRYPT_CREDENTIAL_KEY');

        if ($method === 'AES-256-CBC') {
            try {
                // Try credential-specific key first, then fallback to APP_KEY
                if ($credentialKey) {
                    return self::decryptWithKey($value, $credentialKey);
                }
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Encrypt with specific key
     */
    private static function encryptWithKey(string $value, string $key): string {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt with specific key
     */
    private static function decryptWithKey(string $value, string $key): string {
        $data = base64_decode($value);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}
