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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Action;

use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Mail;
use Pimcore\Model\Document\Email;

class SendMail extends AbstractAction
{
    const OPTION_CONSIDER_PROFILING_CONSENT = 'considerProfilingConsent';
    const OPTION_EMAIL_DOCUMENT = 'emailDoc';
    const OPTION_SEND_TO_CUSTOMER = 'toCustomer';
    protected $name = 'SendMail';

    public function process(
        ActionDefinitionInterface $actionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $options = $actionDefinition->getOptions();
        if (isset($options[self::OPTION_CONSIDER_PROFILING_CONSENT]) && $options[self::OPTION_CONSIDER_PROFILING_CONSENT] !== false && !$this->consentChecker->hasProfilingConsent($customer)) {
            return;
        }

        if (empty($options[self::OPTION_EMAIL_DOCUMENT])) {
            $this->logger->error($this->name . ' action: emailDoc option not set');

            return;
        } else {

            // Replace placeholders with values of customer object fields
            $mailDocPath = $options[self::OPTION_EMAIL_DOCUMENT];
            preg_match_all('/\%(.*?)\%/', $mailDocPath, $matches);

            foreach ($matches[1] as $field) {
                $getter = 'get' . ucfirst($field);
                if (method_exists($customer, $getter) && $customer->$getter() != '') { // WARNING if value is 0 email will also not be sent
                    $mailDocPath = str_replace('%' . $field . '%', $customer->$getter(), $mailDocPath);
                } else {
                    // if one of the required fields does not exist or is empty email will not be sent
                    $this->logger->error($field . ' is empty for customer ID ' . $customer->getId());

                    return;
                }
            }

            $mailDoc = Email::getByPath($mailDocPath);
            if (!$mailDoc instanceof Email) {
                $this->logger->error($this->name . ' action: mailDoc option must be a Email Document');

                return;
            }
        }

        // prepare Params for email
        $params = $options;
        $params['object'] = $customer;
        $params['emailAddress'] = $customer->getEmail();
        $params['idEncoded'] = $customer->getIdEncoded();
        $params['firstname'] = $customer->getFirstname();
        $params['lastname'] = $customer->getLastname();

        // Send Email
        $mail = new Mail();
        $mail->setDocument($mailDoc);
        $mail->setParams($params);
        if ($options[self::OPTION_SEND_TO_CUSTOMER]) {
            $mail->addTo($customer->getEmail());
        }
        $mail->send();
    }
}
