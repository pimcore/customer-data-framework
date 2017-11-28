<?php
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

namespace CustomerManagementFrameworkBundle\GDPR\DataProvider;

use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use GDPRDataExtractorBundle\DataProvider\DataObjects;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data\MultihrefMetadata;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;
use Pimcore\Model\Search\Backend\Data;

class Customers extends DataObjects
{

    /**
     * @var string
     */
    protected $customerClassName = "";

    /**
     * @var ActivityStoreInterface
     */
    protected $activityStore = null;


    public function __construct(\Pimcore\Model\Webservice\Service $service, CustomerProviderInterface $customerProvider, ActivityStoreInterface $activityStore, array $config)
    {
        $customerClassId = $customerProvider->getCustomerClassId();
        $this->customerClassName = ClassDefinition::getById($customerClassId)->getName();

        $this->service = $service;
        $this->activityStore = $activityStore;

        $config['include'] = true;
        $this->config = [
            'classes' => [
                $this->customerClassName => $config
            ]
        ];
    }

    public function getName() {
        return "customers";
    }

    public function getJsClassName() {
        return "pimcore.plugin.GDPRDataExtractorBundle.dataproviders.customers";
    }


    /**
     * Exports data of given object as json including all references that are configured to be included
     *
     * @param AbstractObject $object
     * @return array
     */
    public function doExportData(AbstractObject $object) {
        $this->exportIds = [];

        $this->fillIds($object);

        $exportResult = [];


        foreach(array_keys($this->exportIds['object']) as $id) {

            $data = $this->service->getObjectConcreteById($id);
            $data = (array)$data;

            if($data['className'] == $this->customerClassName) {
                $list = $this->activityStore->getActivityList();
                $list->setCondition("customerId = " . intval($data['id']));

                $data['activities'] = [];
                foreach ($list as $activity) {
                    $activityData = $activity->getData();
                    $activityData['attributes'] = $activity->getAttributes();
                    unset($activityData['implementationClass']);
                    $data['activities'][] = $activityData;
                }
            }

            $exportResult[] = $data;
        }
        if($this->exportIds['image']) {
            foreach(array_keys($this->exportIds['image']) as $id) {
                $exportResult[] = $this->service->getAssetFileById($id);
            }
        }

        return $exportResult;
    }

}