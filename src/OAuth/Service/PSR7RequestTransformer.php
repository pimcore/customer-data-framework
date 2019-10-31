<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 14.06.2018
 * Time: 11:29
 */

namespace CustomerManagementFrameworkBundle\OAuth\Service;

use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class PSR7RequestTransformer {

    /**
     * @var HttpMessageFactoryInterface
     */
    private $diactorosFactory = null;

    public function __construct(HttpMessageFactoryInterface $diactorosFactory)
    {
        $this->diactorosFactory = $diactorosFactory;
    }

    public function transformToPSR7Request($symfonyRequest)
    {
        return $this->diactorosFactory->createRequest($symfonyRequest);
    }

    public function getPSR7Response()
    {
        $symfonyResponse = new Response();
        return $this->diactorosFactory->createResponse($symfonyResponse);
    }

}