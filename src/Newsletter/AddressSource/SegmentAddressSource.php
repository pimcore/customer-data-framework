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

namespace CustomerManagementFrameworkBundle\Newsletter\AddressSource;

use CustomerManagementFrameworkBundle\DataValidator\EmailValidator;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Bundle\NewsletterBundle\Document\Newsletter\AddressSourceAdapterInterface;
use Pimcore\Bundle\NewsletterBundle\Document\Newsletter\SendingParamContainer;
use Pimcore\Db;

class SegmentAddressSource implements AddressSourceAdapterInterface
{
    /* @var SegmentManagerInterface */
    private $segmentManager = null;

    /* @var SendingParamContainer[] */
    private $sendingParamContainers = [];

    /**
     * @param array $arguments ['segmentIds' => string[], 'operator' => string, 'filterFlags' => string[]]
     */
    public function __construct(array $arguments)
    {
        $operator = $arguments['operator'];
        if ($operator == 'and') {
            $operator = SegmentManagerInterface::CONDITION_AND;
        } else {
            $operator = SegmentManagerInterface::CONDITION_OR;
        }
        $this->segmentManager = \Pimcore::getContainer()->get(SegmentManagerInterface::class);
        $this->sendingParamContainers = $this->setUpSendingParamContainers(
            array_filter($arguments['segmentIds']),
            $operator,
            $arguments['filterFlags']
        );
    }

    /**
     * @inheritDoc
     */
    public function getMailAddressesForBatchSending(): array
    {
        return $this->sendingParamContainers;
    }

    /**
     * @inheritDoc
     */
    public function getParamsForTestSending($emailAddress): SendingParamContainer
    {
        return new SendingParamContainer($emailAddress, ['emailAddress' => $emailAddress]);
    }

    /**
     * @inheritDoc
     */
    public function getTotalRecordCount(): int
    {
        return count($this->sendingParamContainers);
    }

    /**
     * @inheritDoc
     */
    public function getParamsForSingleSending($limit, $offset): array
    {
        return array_slice($this->sendingParamContainers, $offset, $limit);
    }

    /**
     * takes an array of segment ids, gets customers for those segments and returns an array of SendingParamContainers
     * containing email addresses that are 'ok'
     *
     * @uses SegmentManagerInterface
     *
     * @param string[]|int[] $segmentIds
     * @param string $operator
     * @param string[] $filterFlags
     *
     * @return SendingParamContainer[]
     */
    private function setUpSendingParamContainers(array $segmentIds, string $operator, array $filterFlags): array
    {
        if (empty($segmentIds)) {
            return [];
        }

        $customerListing = $this->segmentManager->getCustomersBySegmentIds($segmentIds, $operator);

        if ($filterFlags) {
            $originalCondition = $customerListing->getCondition();
            $conditionParts = [];
            $db = Db::get();
            foreach ($filterFlags as $filterFlag) {
                $conditionParts[] = $db->quoteIdentifier($filterFlag) . ' = 1';
            }

            $condition = '(' . $originalCondition . ') AND (' . implode(' AND ', $conditionParts) . ')';
            $customerListing->setCondition($condition);
        }

        return
            array_filter(
                array_map(
                    function ($customer) {
                        if (!$customer instanceof CustomerInterface) {
                            return null;
                        }

                        $validator = new EmailValidator();
                        if (!$validator->isValid($customer->getEmail())) {
                            return null;
                        }

                        return new SendingParamContainer($customer->getEmail(), ['emailAddress' => $customer->getEmail(), 'ID_ENCODED' => $customer->getIdEncoded()]);
                    },
                    $customerListing->getObjects() ?? []
                )
            );
    }
}
