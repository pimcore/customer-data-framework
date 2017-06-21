<?php

namespace CustomerManagementFrameworkBundle\RESTApi\Traits;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\View\Helper\Url;

trait ResourceUrlGenerator
{

    /**
     * @var string
     */
    protected $apiResourceRoute;


    /**
     * @param string $apiResourceRoute
     * @return $this
     */
    public function setApiResourceRoute($apiResourceRoute)
    {
        $this->apiResourceRoute = $apiResourceRoute;

        return $this;
    }


    /**
     * Generate record URL
     *
     * @param int $id
     * @return string|null
     */
    protected function generateResourceApiUrl($id)
    {
        if (!$this->apiResourceRoute) {
            return null;
        }

        return \Pimcore::getContainer()->get('router')->generate($this->apiResourceRoute, [
            'id' => $id
        ]);
    }
}
