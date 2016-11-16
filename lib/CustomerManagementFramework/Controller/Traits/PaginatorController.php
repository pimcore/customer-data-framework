<?php

namespace CustomerManagementFramework\Controller\Traits;

use CustomerManagementFramework\Listing\Listing;

trait PaginatorController
{
    /**
     * Build object paginator for filtered list
     *
     * @param mixed $data
     * @param int $defaultPageSize
     * @return \Zend_Paginator
     */
    protected function buildPaginator($data, $defaultPageSize = Listing::DEFAULT_PAGE_SIZE)
    {
        /** @var \Zend_Controller_Action $this */
        $paginator = \Zend_Paginator::factory($data);
        $paginator->setItemCountPerPage((int)$this->getParam('perPage', $defaultPageSize));
        $paginator->setCurrentPageNumber((int)$this->getParam('page', 1));

        return $paginator;
    }
}
