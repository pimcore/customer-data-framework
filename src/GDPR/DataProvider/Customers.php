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

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\GDPR\DataProvider;

use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Service\ObjectToArray;
use Pimcore\Bundle\AdminBundle\GDPR\DataProvider\DataObjects;
use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Concrete;

class Customers extends DataObjects
{
    /**
     * @var string
     */
    protected $customerClassName = '';

    /**
     * @var ActivityStoreInterface
     */
    protected $activityStore = null;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider = null;

    public function __construct(CustomerProviderInterface $customerProvider, ActivityStoreInterface $activityStore, array $config)
    {
        $this->customerProvider = $customerProvider;
        $customerClassId = $customerProvider->getCustomerClassId();
        $this->customerClassName = ClassDefinition::getById($customerClassId)->getName();

        $this->activityStore = $activityStore;

        $config['include'] = true;
        $this->config = [
            'classes' => [
                $this->customerClassName => $config
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'customers';
    }

    /**
     * @inheritdoc
     */
    public function getJsClassName(): string
    {
        return 'pimcore.plugin.GDPRDataExtractorBundle.dataproviders.customers';
    }

    /**
     * @inheritdoc
     */
    public function getSortPriority(): int
    {
        return 5;
    }

    /**
     * Exports data of given object as json including all references that are configured to be included
     *
     * @param AbstractObject $object
     *
     * @return array
     */
    public function doExportData(AbstractObject $object): array
    {
        $this->exportIds = [];

        $this->fillIds($object);

        $exportResult = [];

        $objectToArrayHelper = ObjectToArray::getInstance();

        if (array_key_exists('object', $this->exportIds)) {
            foreach (array_keys($this->exportIds['object']) as $id) {
                $object = Concrete::getById($id);
                if (!empty($object)) {
                    $data = [
                        'className' => $object->getClass()->getName()
                    ];
                    $data['data'] = $objectToArrayHelper->toArray($object);

                    if ($data['className'] == $this->customerClassName) {
                        $list = $this->activityStore->getActivityList();
                        $list->setCondition('customerId = ' . intval($data['data']['id']));

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
            }
        }

        return $exportResult;
    }

    /**
     * @param int $id
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param int $start
     * @param int $limit
     * @param string|null $sort
     *
     * @return array
     */
    public function searchData(int $id, string $firstname, string $lastname, string $email, int $start, int $limit, string $sort = null): array
    {
        $listing = $this->customerProvider->getList();

        if (!empty($id)) {
            $listing->setCondition('id = :id', ['id' => $id]);
        }

        if (!empty($firstname)) {
            $listing->setCondition('firstname = :firstname', ['firstname' => $firstname]);
        }

        if (!empty($lastname)) {
            $listing->setCondition('lastname = :lastname', ['lastname' => $lastname]);
        }

        if (!empty($email)) {
            $listing->setCondition('email = :email', ['email' => $email]);
        }

        $sortingSettings = QueryParams::extractSortingSettings(['sort' => $sort]);
        if ($sortingSettings['orderKey']) {
            // we need a special mapping for classname as this is stored in subtype column
            $sortMapping = [
                'classname' => 'subtype',
            ];

            $sort = $sortingSettings['orderKey'];
            if (array_key_exists($sortingSettings['orderKey'], $sortMapping)) {
                $sort = $sortMapping[$sortingSettings['orderKey']];
            }

            $order = $sortingSettings['order'] ?? null;

            $listing->setOrderKey($sort);
            $listing->setOrder($order);
        }

        $listing->setOffset($start);
        $listing->setLimit($limit);

        $elements = [];
        if ($listing->count()) {
            foreach ($listing->getData() as $customer) {
                $data = DataObject\Service::gridObjectData($customer);
                $data['__gdprIsDeletable'] = $this->config['classes'][$customer->getClassName()]['allowDelete'] ?? false;
                $elements[] = $data;
            }
        }

        return ['data' => $elements, 'success' => true, 'total' => $listing->count()];
    }
}
