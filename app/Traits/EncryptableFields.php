<?php
// app/Traits/EncryptableFields.php

namespace App\Traits;

use App\Services\EncryptionService;

trait EncryptableFields
{
    /**
     * Chiffre automatiquement les champs définis
     * dans $encryptable lors de la sauvegarde
     */
    protected static function bootEncryptableFields(): void
    {
        $service = app(EncryptionService::class);

        // AVANT création ou mise à jour → chiffrer
        static::saving(function ($model) use ($service) {
            foreach ($model->encryptable ?? [] as $field) {
                if (isset($model->attributes[$field])
                    && !$service->isEncrypted($model->attributes[$field])
                ) {
                    $model->attributes[$field] =
                        $service->encrypt($model->attributes[$field]);
                }
            }
        });
    }

    /**
     * Accesseur dynamique : déchiffre à la volée
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable ?? [])) {
            $service = app(EncryptionService::class);

            if (!$service->isEncrypted($value)) {
                return $value;
            }

            return $service->decrypt($value);
        }

        return $value;
    }
}
