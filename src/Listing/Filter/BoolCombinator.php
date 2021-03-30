<?php


namespace CustomerManagementFrameworkBundle\Listing\Filter;


use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Db;
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


    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        if(count($this->filters) === 1) {
            $filter = $this->filters[0];
            $filter->applyOnCreateQuery($listing, $queryBuilder);
        } else if(count($this->filters)) {
            $queryParts = [];
            foreach($this->filters as $filter) {
                $subQuery = Db::get()->createQueryBuilder();
                $filter->applyOnCreateQuery($listing, $subQuery);
                $queryParts[] = $subQuery->getQueryPart('where');
            }
            $queryBuilder->andWhere(implode(' ' . $this->operator . ' ', $queryParts));
        }
    }
}
