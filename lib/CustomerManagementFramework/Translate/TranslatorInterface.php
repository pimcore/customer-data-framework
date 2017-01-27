<?php

namespace CustomerManagementFramework\Translate;

interface TranslatorInterface
{
    /**
     * Translates a message. Optional parameters are passed to sprintf().
     *
     * @param string $messageId
     * @param array|mixed $parameters
     * @return string
     */
    public function translate($messageId, $parameters = []);
}
