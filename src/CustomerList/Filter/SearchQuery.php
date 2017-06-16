<?php

namespace CustomerManagementFrameworkBundle\CustomerList\Filter;

use BackendToolkit\Listing\Filter\AbstractFilter;
use BackendToolkit\Listing\OnCreateQueryFilterInterface;
use CustomerManagementFrameworkBundle\CustomerList\Filter\Exception\SearchQueryException;
use Phlexy\LexingException;
use Pimcore\Model\Object\Listing as CoreListing;
use SearchQueryParser\ParserException;
use SearchQueryParser\QueryBuilder\ZendDbSelect;
use SearchQueryParser\SearchQueryParser;

class SearchQuery extends AbstractFilter implements OnCreateQueryFilterInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var string
     */
    protected $parsedQuery;

    /**
     * @param array $fields
     * @param string $query
     */
    public function __construct(array $fields, $query)
    {
        $this->fields      = $fields;
        $this->query       = $query;
        $this->parsedQuery = $this->parseQuery($query);
    }

    /**
     * @inheritDoc
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, \Zend_Db_Select $query)
    {
        $queryBuilder = new ZendDbSelect($this->fields, [
            'stripWildcards' => false // allow LIKE wildcards
        ]);

        $queryBuilder->processQuery($query, $this->parsedQuery);
    }

    /**
     * @param string $queryString
     * @return \SearchQueryParser\Part\Query
     */
    protected function parseQuery($queryString)
    {
        try {
            return SearchQueryParser::parseQuery($queryString);
        } catch (LexingException $e) {
            $this->handleParserException($e);
        } catch (ParserException $e) {
            $this->handleParserException($e);
        }
    }

    /**
     * @param \Exception $e
     * @throws SearchQueryException
     */
    protected function handleParserException(\Exception $e)
    {
        $message = $e->getMessage();

        if ($e instanceof LexingException) {
            $message = preg_replace('/on line \d+$/', '', $message);
        }

        throw new SearchQueryException($message, $e->getCode(), $e);
    }
}
