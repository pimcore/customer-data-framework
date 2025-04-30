<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Helper;

use Pimcore\Model\Element\ElementInterface;

class Notes
{
    /**
     * @param string $type
     * @param string $title
     * @param string|null $description
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
        $note->setDescription((string) $description);

        return $note;
    }
}
