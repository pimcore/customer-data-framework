<?php

namespace CustomerManagementFramework\View\Helper;

use CustomerManagementFramework\Listing\Listing;

class FilterFormAction extends \Zend_View_Helper_Abstract
{
    /**
     * @param \Zend_Paginator $paginator
     * @return string
     */
    public function filterFormAction(\Zend_Paginator $paginator)
    {
        // reset page when changing filters
        $formActionParams = [
            'page'    => null,
            'perPage' => null
        ];

        if (null !== $paginator && $paginator->getItemCountPerPage() !== Listing::DEFAULT_PAGE_SIZE) {
            $formActionParams['perPage'] = $paginator->getItemCountPerPage();
        }

        $formAction = $this->view->url($formActionParams);

        return $formAction;
    }
}
