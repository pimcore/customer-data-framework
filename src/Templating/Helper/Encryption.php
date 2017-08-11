<?php

declare(strict_types=1);

namespace CustomerManagementFrameworkBundle\Templating\Helper;


use CustomerManagementFrameworkBundle\Encryption\EncryptionServiceInterface;
use Defuse\Crypto\Key;
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
