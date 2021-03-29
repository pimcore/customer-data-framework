<?php


namespace CustomerManagementFrameworkBundle\Listing\Filter;


use Pimcore\Db;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;

class BoolCombinator extends AbstractFilter implements OnCreateQueryFilterInterface
{

    /**
     * @var OnCreateQueryFilterInterface[]
     */
    protected $filters;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @param OnCreateQueryFilterInterface[] $filters
     * @param string $operator
     */
    public function __construct(array $filters, string $operator = 'AND')
    {
        $this->filters = $filters;

        foreach($this->filters as $filter) {
            if (!$filter instanceof OnCreateQueryFilterInterface) {
                throw new \Exception('Invalid filter, does not implement OnCreateQueryFilterInterface');
            }
        }

        $this->operator = $operator;
    }


    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $query)
    {
        if(count($this->filters) === 1) {
            $filter = $this->filters[0];
            $filter->applyOnCreateQuery($listing, $query);
        } else if(count($this->filters)) {
            $queryParts = [];
            foreach($this->filters as $filter) {
                $subQuery = Db::get()->select();
                $filter->applyOnCreateQuery($listing, $subQuery);
                $queryParts[] = implode(' ', $subQuery->getPart(Db\ZendCompatibility\QueryBuilder::WHERE));
            }
            $query->where(implode(' ' . $this->operator . ' ', $queryParts));
        }
    }
}
