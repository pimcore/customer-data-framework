<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

namespace CustomerManagementFrameworkBundle\Event;

use Pimcore\Event\Model\ElementEventInterface;

interface PimcoreElementRemovalListenerInterface {

    /**
     * performs cleaning up when Pimcore elements are deleted,
     * namely removes segment assignments from assignment, queue and index tables
     *
     * @param ElementEventInterface $event
     * @return void
     */
    public function onPostDelete(ElementEventInterface $event);
}
