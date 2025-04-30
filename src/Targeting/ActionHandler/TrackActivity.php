<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Targeting\ActionHandler;

use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\GDPR\Consent\ConsentCheckerInterface;
use CustomerManagementFrameworkBundle\Model\Activity\GenericActivity;
use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use Pimcore\Bundle\PersonalizationBundle\Model\Tool\Targeting\Rule;
use Pimcore\Bundle\PersonalizationBundle\Targeting\ActionHandler\ActionHandlerInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataLoaderInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class TrackActivity implements ActionHandlerInterface, DataProviderDependentInterface
{
    /**
     * @var ActivityManagerInterface
     */
    protected $activityManager;

    /**
     * @var DataLoaderInterface
     */
    protected $dataLoader;

    /**
     * @var ConsentCheckerInterface
     */
    protected $consentChecker;

    public function __construct(ActivityManagerInterface $activityManager, DataLoaderInterface $dataLoader, ConsentCheckerInterface $consentChecker)
    {
        $this->activityManager = $activityManager;
        $this->dataLoader = $dataLoader;
        $this->consentChecker = $consentChecker;
    }

    public function getDataProviderKeys(): array
    {
        return [Customer::PROVIDER_KEY];
    }

    public function apply(VisitorInfo $visitorInfo, array $action, ?Rule $rule = null): void
    {
        //get customer
        $this->dataLoader->loadDataFromProviders($visitorInfo, [Customer::PROVIDER_KEY]);
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if (!$customer) {
            return;
        }

        if (isset($action['considerProfilingConsent']) && $action['considerProfilingConsent'] !== false && !$this->consentChecker->hasProfilingConsent($customer)) {
            return;
        }

        $activityType = $action['activityType'];
        if (empty($activityType)) {
            return;
        }

        $activity = new GenericActivity([
            'type' => $activityType,
            'attributes' => [],
        ]);
        $activity->setCustomer($customer);

        $this->activityManager->trackActivity($activity);
    }
}
