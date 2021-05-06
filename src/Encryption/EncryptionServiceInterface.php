<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
