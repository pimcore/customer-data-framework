<?php

namespace CustomerManagementFramework\View\Helper;

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

        if (null !== $paginator && $paginator->getItemCountPerPage() !== $this->view->defaultPageSize()) {
            $formActionParams['perPage'] = $paginator->getItemCountPerPage();
        }

        $formAction = $this->view->url($formActionParams);

        return $formAction;
    }
}
