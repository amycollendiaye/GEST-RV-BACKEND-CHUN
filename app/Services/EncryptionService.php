<?php
// app/Services/EncryptionService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EncryptionService
{
    private string $key;
    private string $cipher = 'aes-256-gcm';

    public function __construct()
    {
        // La clé vient du fichier .env — jamais dans le code
        $keyBase64 = config('app.encryption_key');

        if (empty($keyBase64)) {
            throw new \RuntimeException(
                'ENCRYPTION_KEY manquant dans .env'
            );
        }

        $this->key = base64_decode($keyBase64);

        if (strlen($this->key) !== 32) {
            throw new \RuntimeException(
                'ENCRYPTION_KEY doit être 32 octets (256 bits)'
            );
        }
    }

    /**
     * Chiffre une valeur avec AES-256-GCM
     * Retourne null si la valeur est null/vide
     */
    public function encrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // IV aléatoire de 12 octets (recommandé pour GCM)
        $iv  = random_bytes(12);
        $tag = '';

        $encrypted = openssl_encrypt(
            $value,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16  // tag length 16 octets
        );

        if ($encrypted === false) {
            Log::error('Échec du chiffrement AES-256-GCM');
            throw new \RuntimeException('Erreur de chiffrement');
        }

        // On concatène IV + TAG + données chiffrées
        // puis on encode en base64 pour stocker en BDD
        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * Déchiffre une valeur chiffrée avec AES-256-GCM
     */
    public function decrypt(?string $encryptedValue): ?string
    {
        if ($encryptedValue === null || $encryptedValue === '') {
            return $encryptedValue;
        }

        $decoded = base64_decode($encryptedValue);

        if ($decoded === false || strlen($decoded) < 28) {
            // Valeur non chiffrée ou corrompue — on retourne telle quelle
            return $encryptedValue;
        }

        // On extrait IV (12 octets) + TAG (16 octets) + données
        $iv        = substr($decoded, 0, 12);
        $tag       = substr($decoded, 12, 16);
        $encrypted = substr($decoded, 28);

        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            Log::warning('Échec du déchiffrement — données corrompues ?');
            return null;
        }

        return $decrypted;
    }

    /**
     * Vérifie si une valeur est déjà chiffrée (base64 valide)
     */
    public function isEncrypted(?string $value): bool
    {
        if (empty($value)) return false;
        $decoded = base64_decode($value, true);
        return $decoded !== false && strlen($decoded) >= 28;
    }
}