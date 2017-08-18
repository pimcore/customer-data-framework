<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Helper;

use Pimcore\Model\Element\ElementInterface;

class Notes
{
    /**
     * @param ElementInterface $element
     * @param                  $type
     * @param                  $title
     * @param null $description
     *
     * @return \Pimcore\Model\Element\Note
     */
    public static function createNote(ElementInterface $element, $type, $title, $description = null)
    {
        $note = new \Pimcore\Model\Element\Note();
        $note->setElement($element);
        $note->setDate(time());
        $note->setType($type);
        $note->setTitle($title);
        $note->setDescription($description);

        return $note;
    }
}
