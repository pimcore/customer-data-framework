<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Action;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class ChangeFieldValue extends AbstractAction
{
    const OPTION_FIELD = 'field';
    const OPTION_VALUE = 'value';

    public function process(ActionDefinitionInterface $actionDefinition, CustomerInterface $customer)
    {
        $options = $actionDefinition->getOptions();

        if ($field = $options[self::OPTION_FIELD]) {
            $setter = 'set'.ucfirst($field);
            $getter = 'get'.ucfirst($field);

            if (method_exists($customer, $setter)) {
                if ($customer->$getter() != $options[self::OPTION_VALUE]) {
                    $this->logger->info(
                        sprintf(
                            "ChangeFieldValue action: change field %s to value '%s'",
                            $field,
                            $options[self::OPTION_VALUE]
                        )
                    );
                    $customer->$setter($options[self::OPTION_VALUE]);
                    $customer->save();
                } else {
                    $this->logger->info(sprintf('ChangeFieldValue action: skipped as data did not change'));
                }
            } else {
                $this->logger->error(sprintf('ChangeFieldValue action: field %s does not exist', $field));
            }
        }
    }
}
