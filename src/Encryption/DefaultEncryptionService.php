<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Encryption;

use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class DefaultEncryptionService implements EncryptionServiceInterface
{
    use LoggerAware;

    /**
     * @var Key
     */
    protected $defaultKey;

    /**
     * @var string
     */
    protected $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Get the default key used for encryption/decryption if no key is passed
     *
     * @return Key
     */
    public function getDefaultKey()
    {
        if (null === $this->defaultKey) {
            $secret = $this->secret;
            if (!$secret || empty($secret)) {
                throw new \RuntimeException('Need an encryption secret');
            }

            $this->defaultKey = Key::loadFromAsciiSafeString($secret);
        }

        return $this->defaultKey;
    }

    /**
     * Encrypt data with key (will fall back to default key if none given)
     *
     * @param string $plaintext
     * @param Key|null $key
     * @param bool $rawBinary
     *
     * @return string
     */
    public function encrypt($plaintext, Key $key = null, $rawBinary = false)
    {
        if (empty($plaintext)) {
            $this->getLogger()->warning('Returning empty encrypt() result as plaintext was empty');

            return '';
        }

        if (!$key) {
            $key = $this->getDefaultKey();
        }

        return Crypto::encrypt($plaintext, $key, $rawBinary);
    }

    /**
     * Decrypt ciphertext with key (will fall back to default key if none given)
     *
     * @param string $ciphertext
     * @param Key|null $key
     * @param bool $rawBinary
     *
     * @return string
     */
    public function decrypt($ciphertext, Key $key = null, $rawBinary = false)
    {
        if (empty($ciphertext)) {
            $this->getLogger()->warning('Returning empty decrypt() result as ciphertext was empty');

            return '';
        }

        if (!$key) {
            $key = $this->getDefaultKey();
        }

        return Crypto::decrypt($ciphertext, $key, $rawBinary);
    }

    /**
     * @return array|null
     *
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.sleep
     */
    public function __sleep()
    {
        // do not serialize default key
        $this->defaultKey = null;
    }
}
