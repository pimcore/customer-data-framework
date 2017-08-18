<?php

declare(strict_types=1);

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
