<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-12
 * Time: 1:36 PM
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\Indexer;

use Pimcore\Model\Element\ElementInterface;

interface IndexerInterface {

    /**
     * indexes all elements in currently stored in the queue
     *
     * @param ElementInterface $rootNode
     * @return bool
     */
    public function processQueue(): bool;
}