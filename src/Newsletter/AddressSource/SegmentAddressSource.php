<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 08.03.2018
 * Time: 11:24
 */

namespace CustomerManagementFrameworkBundle\Newsletter\AddressSource;

use CustomerManagementFrameworkBundle\DataValidator\EmailValidator;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Document\Newsletter\AddressSourceAdapterInterface;
use Pimcore\Document\Newsletter\SendingParamContainer;
class SegmentAddressSource implements AddressSourceAdapterInterface
{
    /* @var SegmentManagerInterface */
    private $segmentManager = null;

    /* @var SendingParamContainer[] */
    private $sendingParamContainers = [];

    private $operator = SegmentManagerInterface::CONDITION_OR;


    /**
     * @param array $arguments ['segmentIds' => string[], 'operator' => string]
     */
    public function __construct(array $arguments)
    {
        $operator = $arguments['operator'];
        if ($operator == "and") {
            $this->operator = SegmentManagerInterface::CONDITION_AND;
        } else {
            $this->operator = SegmentManagerInterface::CONDITION_OR;
        }
        $this->segmentManager = \Pimcore::getContainer()->get(SegmentManagerInterface::class);
        $this->sendingParamContainers = $this->setUpSendingParamContainers(array_filter($arguments['segmentIds']));
    }

    /**
     * @inheritDoc
     */
    public function getMailAddressesForBatchSending()
    {
        return $this->sendingParamContainers;
    }

    /**
     * @inheritDoc
     */
    public function getParamsForTestSending($emailAddress)
    {
        return new SendingParamContainer($emailAddress, ['emailAddress' => $emailAddress]);
    }

    /**
     * @inheritDoc
     */
    public function getTotalRecordCount()
    {
        return count($this->sendingParamContainers);
    }

    /**
     * @inheritDoc
     */
    public function getParamsForSingleSending($limit, $offset)
    {
        return array_slice($this->sendingParamContainers, $offset, $limit);
    }

    /**
     * takes an array of segment ids, gets customers for those segments and returns an array of SendingParamContainers
     * containing email addresses that are 'ok'
     *
     * @uses SegmentManagerInterface
     * @param string[]|int[] $segmentIds
     * @return SendingParamContainer[]
     */
    private function setUpSendingParamContainers(array $segmentIds): array
    {
        if(empty($segmentIds)) {
            return [];
        }

        return
            array_filter(
                array_map(
                    function ($customer) {
                        /* @var $customer CustomerInterface */
                        if(! $customer instanceof CustomerInterface) {
                            return null;
                        }

                        $validator = new EmailValidator();
                        if(!$validator->isValid($customer->getEmail())) {
                            return null;
                        }

                        return new SendingParamContainer($customer->getEmail(), ['emailAddress' => $customer->getEmail()]);
                    },
                    $this->segmentManager->getCustomersBySegmentIds($segmentIds, $this->operator)->getObjects() ?? []
                )
            );
    }

}