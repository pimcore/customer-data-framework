<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use CustomerManagementFrameworkBundle\Encryption\EncryptionServiceInterface;
use Symfony\Component\Templating\Helper\Helper;

class Encryption extends Helper
{
    /**
     * @var EncryptionServiceInterface
     */
    private $encryptionService;

    public function __construct(EncryptionServiceInterface $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function getName()
    {
        return 'cmfEncryption';
    }

    public function decrypt($ciphertext)
    {
        return $this->encryptionService->decrypt($ciphertext);
    }
}
