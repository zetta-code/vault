<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Service;

use Zend\Crypt\BlockCipher;

/**
 * Crypt
 **/
class Crypt
{
    /**
     * The encryption key.
     *
     * @var string
     */
    protected $key;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher;

    /**
     * Create a new encrypter instance.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return void
     *
     * @throws \RuntimeException
     */
    public function __construct($key, $cipher = 'AES-128-CBC')
    {
        $key = (string) $key;

        if ($this->supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
        } else {
            throw new \RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    public function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) || ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Create a new encryption key for the given cipher.
     *
     * @param  string  $cipher
     * @return string
     */
    public function generateKey($cipher)
    {
        return openssl_random_pseudo_bytes($cipher == 'AES-128-CBC' ? 16 : 32);
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     */
    public function encrypt($value, $serialize = true)
    {
        $blockCipher = BlockCipher::factory('openssl', ['algo' => 'aes']);
        $blockCipher->setKey($this->key);
        return $blockCipher->encrypt($serialize ? serialize($value) : $value);
    }

    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $unserialize
     * @return string
     */
    public function decrypt($value, $unserialize = true)
    {
        $blockCipher = BlockCipher::factory('openssl', ['algo' => 'aes']);
        $blockCipher->setKey($this->key);
        $decrypted = $blockCipher->decrypt($value);

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $value
     * @return string
     */
    public function decryptString($value)
    {
        return $this->decrypt($value, false);
    }

    /**
     * Get the Crypt key
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the Crypt key
     * @param string $key
     * @return Crypt
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function generateRandomKey()
    {
        return 'base64:'.base64_encode(
                $this->generateKey($this->cipher)
            );
    }
}
