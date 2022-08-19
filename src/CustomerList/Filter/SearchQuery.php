<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\CustomerList\Filter;

use CustomerManagementFrameworkBundle\CustomerList\Filter\Exception\SearchQueryException;
use CustomerManagementFrameworkBundle\Listing\Filter\AbstractFilter;
use CustomerManagementFrameworkBundle\Listing\Filter\OnCreateQueryFilterInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Phlexy\LexingException;
use Pimcore\Model\DataObject\Listing as CoreListing;
use SearchQueryParser\ParserException;
use SearchQueryParser\QueryBuilder\Doctrine;
use SearchQueryParser\SearchQueryParser;

class SearchQuery extends AbstractFilter implements OnCreateQueryFilterInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var \SearchQueryParser\Part\Query
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

    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        if (sizeof($this->fields) === 1 && preg_match('/AND|OR|!|\(.*\)/', $this->query) === 0) {
            if (strpos($this->query, '*') !== false) {
                $queryBuilder->andWhere(sprintf('%s like %s', $this->fields[0], $listing->quote(str_replace('*', '%', $this->query))));
            } else {
                $queryBuilder->andWhere(sprintf('%s = %s', $this->fields[0], $listing->quote(preg_replace('/^"(.*)"$/', '$1', $this->query))));
            }

            return;
        }

        $parserQueryBuilder = new Doctrine(
            $this->fields,
            [
                'stripWildcards' => false // allow LIKE wildcards
            ]
        );

        $parserQueryBuilder->processQuery($queryBuilder, $this->parsedQuery);
    }

    /**
     * @param string $queryString
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
