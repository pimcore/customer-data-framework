<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
     * @param bool $rawBinary
     *
     * @return string
     */
    public function encrypt($plaintext, ?Key $key = null, $rawBinary = false);

    /**
     * Decrypt ciphertext with key (will fall back to default key if none given)
     *
     * @param string $ciphertext
     * @param bool $rawBinary
     *
     * @return string
     */
    public function decrypt($ciphertext, ?Key $key = null, $rawBinary = false);
}
