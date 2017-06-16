<?php

namespace CustomerManagementFrameworkBundle\RESTApi\Traits;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\View\Helper\Url;

trait ResourceUrlGenerator
{
    /**
     * @var string
     */
    protected $apiRoute;

    /**
     * @var string
     */
    protected $apiResourceRoute;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * @param string $apiRoute
     * @return $this
     */
    public function setApiRoute($apiRoute)
    {
        $this->apiRoute = $apiRoute;

        return $this;
    }

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
     * @param Url $urlHelper
     * @return $this
     */
    public function setUrlHelper(Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;

        return $this;
    }

    /**
     * Generate API URL, appending an optional path
     *
     * @param string|null $path
     * @param array $params
     * @return string|null
     */
    protected function generateApiUrl($path = '', array $params = [])
    {
        if (!$this->urlHelper || !$this->apiRoute) {
            return null;
        }

        $url = $this->urlHelper->url($params, $this->apiRoute, true);

        if (!empty($url) && !empty($path)) {
            $path = ltrim($path, '/');
            $url  = $url . '/' . $path;
        }

        return $url;
    }

    /**
     * Generate record URL
     *
     * @param ElementInterface $element
     * @return string|null
     */
    protected function generateElementApiUrl(ElementInterface $element)
    {
        if (!$this->urlHelper || !$this->apiResourceRoute) {
            return null;
        }

        return $this->urlHelper->url([
            'id' => $element->getId()
        ], $this->apiResourceRoute, true);
    }
}
