<?php

namespace CustomerManagementFramework\RESTApi;

interface HandlerInterface
{
    /**
     * @param \Zend_Controller_Request_Http $request
     * @return Response
     *
     * @throws Exception\ExceptionInterface
     * @throws \RuntimeException
     */
    public function handle(\Zend_Controller_Request_Http $request);
}
