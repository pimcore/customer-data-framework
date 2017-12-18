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

namespace CustomerManagementFrameworkBundle\CustomerList\Filter;

use CustomerManagementFrameworkBundle\CustomerList\Filter\Exception\SearchQueryException;
use CustomerManagementFrameworkBundle\Listing\Filter\AbstractFilter;
use CustomerManagementFrameworkBundle\Listing\Filter\OnCreateQueryFilterInterface;
use Phlexy\LexingException;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;
use SearchQueryParser\ParserException;
use SearchQueryParser\QueryBuilder\ZendCompatibility;
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
     * @var string
     */
    protected $query;

    /**
     * @param array $fields
     * @param string $query
     */
    public function __construct(array $fields, $query)
    {
        $this->fields = $fields;
        $this->query = $query;
        $this->parsedQuery = $this->parseQuery($query);
    }

    /**
     * @inheritDoc
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $query)
    {
        // for single fields directly check field content without overhead of parsing
        if(sizeof($this->fields) === 1) {
            if(strpos($this->query, '*') !== false) {
                $query->where(sprintf('%s like ?', $this->fields[0]), str_replace('*', '%', $this->query));
            } else {
                $query->where(sprintf('%s = ?', $this->fields[0]), $this->query);
            }
            return;
        }

        $queryBuilder = new ZendCompatibility(
            $this->fields,
            [
            'stripWildcards' => false // allow LIKE wildcards
        ]
        );

        $queryBuilder->processQuery($query, $this->parsedQuery);
    }

    /**
     * @param string $queryString
     *
     * @return \SearchQueryParser\Part\Query
     */
    protected function parseQuery($queryString)
    {
        try {
            //$queryString = str_replace('')
            return SearchQueryParser::parseQuery($queryString);
        } catch (LexingException $e) {
            $this->handleParserException($e);
        } catch (ParserException $e) {
            $this->handleParserException($e);
        }
    }

    /**
     * @param \Exception $e
     *
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
