<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Encryption;

use Defuse\Crypto\Key;

interface EncryptionServiceInterface
{
    /**
     * Get the default key used for encryption/decryption if no key is passed
     *
     * @return Key
     */
    public function getDefaultKey();

    /**
     * Encrypt data with key (will fall back to default key if none given)
     *
     * @param string $plaintext
     * @param Key|null $key
     * @param bool $rawBinary
     *
     * @return string
     */
    public function encrypt($plaintext, Key $key = null, $rawBinary = false);

    /**
     * Decrypt ciphertext with key (will fall back to default key if none given)
     *
     * @param string $ciphertext
     * @param Key|null $key
     * @param bool $rawBinary
     *
     * @return string
     */
    public function decrypt($ciphertext, Key $key = null, $rawBinary = false);
}
